import numpy as np
import scipy.integrate as integrate
import re
from .severity_scorer import SeverityScorer

class KBotIntelligence:
    def __init__(self):
        self.severity_scorer = SeverityScorer()
        # Basis data gejala vs. dokter spesialis (dummy vector space)
        # Vector features: [Demam, Batuk, Nyeri, Pusing, Mual, Hamil/Kandungan, Gizi/Tumbuh Kembang, Endemik/Malaria]
        self.symptom_space = {
            "Dr. Budi (Umum)": np.array([1.0, 1.0, 0.2, 0.5, 0.3, 0.0, 0.1, 0.2]),
            "Dr. Siti (Saraf)": np.array([0.1, 0.0, 0.8, 0.9, 0.2, 0.0, 0.0, 0.0]),
            "Dr. Andi (Pencernaan)": np.array([0.2, 0.0, 0.5, 0.2, 1.0, 0.0, 0.1, 0.0]),
            "Bidan Yuli (Poli Kebidanan)": np.array([0.1, 0.0, 0.4, 0.2, 0.6, 1.0, 0.2, 0.0]),
            "Dr. Santoso (Spesialis Tropis - Daerah 3T)": np.array([1.0, 0.2, 0.8, 0.6, 0.7, 0.0, 0.3, 1.0]),
            "Ahli Gizi Rita (Poli Gizi)": np.array([0.0, 0.0, 0.0, 0.1, 0.1, 0.2, 1.0, 0.0])
        }

    def _cosine_similarity(self, vec_a, vec_b):
        """Aljabar Linear: Cosine Similarity antar dua vektor"""
        dot_product = np.dot(vec_a, vec_b)
        norm_a = np.linalg.norm(vec_a)
        norm_b = np.linalg.norm(vec_b)
        if norm_a == 0 or norm_b == 0:
            return 0.0
        return dot_product / (norm_a * norm_b)

    def analyze_symptoms_matrix(self, text):
        """Memetakan teks ke vektor fitur dan mencari kecocokan tertinggi (Aljabar Linear)"""
        # Parsing teks sederhana ke vektor (Demam, Batuk, Nyeri, Pusing, Mual, Hamil, Gizi, Endemik)
        t = text.lower()
        
        # Ekstraksi dan setting nilai fitur (0.0 - 1.0)
        demam = 1.0 if "demam" in t or "panas" in t else 0.0
        batuk = 1.0 if "batuk" in t else 0.0
        nyeri = 1.0 if "nyeri" in t or "sakit" in t else 0.0
        pusing = 1.0 if "pusing" in t else 0.0
        mual = 1.0 if "mual" in t or "muntah" in t else 0.0
        hamil = 1.0 if "hamil" in t or "kandungan" in t or "nifas" in t or "melahirkan" in t else 0.0
        gizi = 1.0 if "kurus" in t or "gizi" in t or "berat badan" in t or "stunting" in t else 0.0
        endemik = 1.0 if "malaria" in t or "dbd" in t or "nyamuk" in t or "tropis" in t or "hutan" in t else 0.0
        
        # Fallback vocabulary lebih kaya:
        if "mules" in t:
            nyeri = max(nyeri, 1.0)
            mual = max(mual, 1.0)
        if "lemas" in t:
            nyeri = max(nyeri, 0.5)
        if "kembung" in t:
            mual = max(mual, 0.8)
        if "bab cair" in t:
            mual = max(mual, 0.8)
            nyeri = max(nyeri, 0.5)
        if "vertigo" in t:
            pusing = max(pusing, 1.0)
        if "migrain" in t:
            pusing = max(pusing, 1.0)
            nyeri = max(nyeri, 0.8)
        if "sesak" in t:
            nyeri = max(nyeri, 0.4)
        if "hamil muda" in t:
            hamil = max(hamil, 1.0)
            mual = max(mual, 0.9)

        vec = np.array([demam, batuk, nyeri, pusing, mual, hamil, gizi, endemik])
        
        best_match = None
        highest_sim = -1
        similarities = {}
        
        for doctor, doc_vec in self.symptom_space.items():
            sim = self._cosine_similarity(vec, doc_vec)
            similarities[doctor] = float(sim)
            if sim > highest_sim:
                highest_sim = sim
                best_match = doctor
                
        return best_match, similarities, vec.tolist()

    def calculate_severity_area(self, text, nlp_confidence=0.5):
        """Menghitung severity score menggunakan SeverityScorer"""
        return self.severity_scorer.score(text, nlp_confidence)

    def classify_severity_quartile(self, score_dict):
        # Sudah dihandle oleh SeverityScorer
        return {"Status": score_dict["status"], "cdc_triage": score_dict["cdc_triage"], "action": score_dict["action"], "raw_score": score_dict["raw_score"]}

    def process_request(self, text, nlp_result=None):
        """Pipeline utama: HIPAA Anonymize -> Vector Mapping -> Severity Area -> IHR/CDC/WHO Standardization"""
        # 1. Kepatuhan Privasi (HIPAA) - PII Scrubber
        # Menghapus pola nomor telepon (10-13 digit) dan alamat email
        text_safe = re.sub(r'\b\d{10,13}\b', '[REDACTED_PHONE]', text)
        text_safe = re.sub(r'\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b', '[REDACTED_EMAIL]', text_safe)
        
        # 2. Aljabar Linear: Pencocokan Vektor Gejala
        recommended_doctor, matrix_sim, symptom_vector = self.analyze_symptoms_matrix(text_safe)
        
        # Override dokter jika nlp_result disediakan
        if nlp_result is not None and "doctor" in nlp_result:
            recommended_doctor = nlp_result["doctor"]
            confidence = nlp_result.get("confidence", 0.5)
        else:
            confidence = 0.5
        
        # 3. Kalkulus: Menghitung Severity Area
        score_dict = self.calculate_severity_area(text_safe, confidence)
        
        # 4. Statistika Dasar & Standar CDC Triage
        quartile_data = self.classify_severity_quartile(score_dict)
        cdc_triage = quartile_data["cdc_triage"]
        action = quartile_data["action"]

        # 5. WHO ICD-10 & IHR (PHEIC)
        # Dictionary sederhana ICD-10
        icd10_code = "R68.89 (Gejala umum lainnya)" # Default
        ihr_status = "Non-PHEIC"
        
        t_lower = text_safe.lower()
        if "demam" in t_lower and ("berdarah" in t_lower or "dbd" in t_lower):
            icd10_code = "A91 (Dengue hemorrhagic fever)"
        elif "hamil" in t_lower or "kandungan" in t_lower:
            icd10_code = "O00-O9A (Pregnancy, childbirth and the puerperium)"
        elif "malaria" in t_lower or "tropis" in t_lower or "wabah" in t_lower or "nyamuk" in t_lower:
            icd10_code = "B50-B54 (Malaria)"
            if "wabah" in t_lower or "menular" in t_lower:
                ihr_status = "PHEIC ALERT (Public Health Emergency)"
        elif "sesak napas" in t_lower and "menular" in t_lower:
             icd10_code = "U07.1 (COVID-19)"
             ihr_status = "PHEIC ALERT (Public Health Emergency)"

        # 6. Generate Gaya Hidup Sehat (Dietary & Lifestyle AI Prescriptions)
        # Jika status IHR adalah Darurat, override rekomendasi menjadi instruksi karantina
        if ihr_status == "PHEIC ALERT (Public Health Emergency)":
            tips_sehat = "[IHR MANDATE] ISOLASI MANDIRI KETAT. Jangan kontak dengan orang lain. Laporkan ke otoritas kesehatan lokal."
            makanan_sehat = "Konsumsi nutrisi tinggi kalori & protein melalui layanan tanpa kontak fisik."
            minuman_sehat = "Hidrasi masif dengan air steril/rebusan matang."
        elif "RED TAG" in cdc_triage:
            tips_sehat = "Istirahat total (Bed Rest), segera periksakan diri ke faskes terdekat."
            makanan_sehat = "Makanan lunak, mudah dicerna (bubur, sup kaldu jernih)."
            minuman_sehat = "Oralit, air kelapa murni, air putih hangat (hindari kafein/gula tinggi)."
        elif "Gizi" in recommended_doctor:
            tips_sehat = "Perhatikan pola makan seimbang 4 sehat 5 sempurna, rutin menimbang berat badan."
            makanan_sehat = "Tinggi protein hewani (telur, ikan), sayuran berdaun hijau gelap, kacang-kacangan."
            minuman_sehat = "Susu tinggi kalsium, jus buah asli tanpa gula tambahan."
        elif "Kebidanan" in recommended_doctor:
            tips_sehat = "Kurangi aktivitas berat, pantau gerakan janin secara berkala, hindari asap rokok."
            makanan_sehat = "Kaya asam folat (brokoli, bayam), zat besi (daging merah matang sempurna), alpukat."
            minuman_sehat = "Air mineral minimal 2.5 liter per hari, susu kehamilan."
        elif "Tropis" in recommended_doctor:
            tips_sehat = "Tidur menggunakan kelambu, hindari gigitan nyamuk, pastikan lingkungan bersih dari genangan air."
            makanan_sehat = "Buah jambu biji, makanan kaya vitamin C untuk imunitas, kurma."
            minuman_sehat = "Banyak minum air putih, rebusan jahe merah."
        else:
            tips_sehat = "Olahraga ringan 30 menit sehari, kelola stres dengan baik."
            makanan_sehat = "Buah-buahan segar, sayuran, dan kurangi makanan olahan/cepat saji."
            minuman_sehat = "Air mineral minimal 2 liter per hari, teh chamomile/hijau tawar."

        ai_lifestyle = {
            "tips_sehat": tips_sehat,
            "makanan_sehat": makanan_sehat,
            "minuman_sehat": minuman_sehat
        }
        
        # Construct Output Response Text
        response_text = (
            f"Sistem mendeteksi kecenderungan penyakit [ICD-10: {icd10_code}]. "
            f"Disarankan menemui **{recommended_doctor}**. "
            f"Berdasarkan standar CDC, Anda masuk kategori **{cdc_triage}**. {action} "
            f"{'(PERINGATAN IHR: KASUS WABAH MUNGKIN TERJADI!)' if ihr_status != 'Non-PHEIC' else ''}"
        )

        parameter_1 = response_text
        parameter_2 = {
            "symptom_vector": symptom_vector,
            "linear_algebra_matrix_similarity": matrix_sim,
            "integral_severity_area_auc": score_dict["raw_score"],
            "statistical_quartiles": quartile_data,
            "ai_lifestyle_prescription": ai_lifestyle,
            "international_standards": {
                "hipaa_anonymized": True,
                "who_icd10": icd10_code,
                "cdc_triage": cdc_triage,
                "ihr_status": ihr_status
            }
        }
        
        return parameter_1, parameter_2
