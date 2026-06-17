import re

class SymptomExtractor:
    def __init__(self):
        # Dictionary of body parts
        self.body_parts = [
            "kepala", "leher", "dada", "perut bagian bawah", "perut", "ulu hati", 
            "pinggang", "punggung", "tangan", "kaki", "lutut", "sendi", "otot",
            "telinga", "mata", "hidung", "tenggorokan", "gigi", "mulut", "kemaluan"
        ]
        
        # Dictionary of symptoms
        self.symptoms = [
            "demam", "panas", "meriang", "batuk", "pilek", "flu", "bersin",
            "pusing", "sakit kepala", "nyeri", "sakit", "pegal", "kram", "linu",
            "mual", "muntah", "diare", "mencret", "kembung", "sembelit", "susah bab",
            "lemas", "lesu", "capek", "kelelahan", "sesak napas", "sulit bernapas",
            "gatal", "ruam", "bengkak", "berdarah", "pendarahan", "bernanah",
            "keputihan", "kesemutan", "mati rasa", "jantung berdebar", "keringat dingin"
        ]

    def extract(self, text: str) -> dict:
        t = text.lower()
        
        extracted = {
            "symptoms": [],
            "body_parts": [],
            "duration": None
        }
        
        # 1. Extract Body Parts
        for bp in self.body_parts:
            # Word boundary check is not perfect in Indonesian but we try a simple regex
            pattern = r'\b' + re.escape(bp) + r'\b'
            if re.search(pattern, t):
                extracted["body_parts"].append(bp)
                
        # 2. Extract Symptoms
        for sym in self.symptoms:
            pattern = r'\b' + re.escape(sym) + r'\b'
            if re.search(pattern, t):
                extracted["symptoms"].append(sym)
                
        # 3. Extract Duration (Regex for "X hari", "X minggu", "X bulan", "X jam", etc.)
        # e.g., "3 hari", "tiga hari", "1 minggu", "sebulan", "sudah 2 hari"
        duration_patterns = [
            r'(\d+)\s*(hari|minggu|bulan|jam|tahun)', # "3 hari"
            r'(satu|dua|tiga|empat|lima|enam|tujuh|delapan|sembilan|sepuluh|sebelas|belasan|puluhan)\s*(hari|minggu|bulan|jam|tahun)', # "tiga hari"
            r'(sehari|seminggu|sebulan|setahun|sejam)', # "sehari"
            r'sudah\s+([a-z\d\s]+)\b' # "sudah lama", "sudah 3 hari" - a bit broad, we will be more specific
        ]
        
        duration_found = None
        # Let's use a more precise regex for duration
        precise_duration_regex = r'\b((?:\d+|satu|dua|tiga|empat|lima|enam|tujuh|delapan|sembilan|sepuluh|sebelas|belasan|puluhan)\s*(?:hari|minggu|bulan|jam|tahun)|sehari|seminggu|sebulan|setahun|sejam|sejak\s+kemarin)\b'
        
        match = re.search(precise_duration_regex, t)
        if match:
            duration_found = match.group(1).strip()
            
        extracted["duration"] = duration_found
        
        # Remove duplicates if any
        extracted["symptoms"] = list(set(extracted["symptoms"]))
        extracted["body_parts"] = list(set(extracted["body_parts"]))
        
        return extracted
