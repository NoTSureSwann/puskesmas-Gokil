import os
import re
import pickle
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.svm import SVC
from sklearn.pipeline import Pipeline
from sklearn.model_selection import StratifiedKFold
from sklearn.metrics import classification_report, accuracy_score, f1_score
from datetime import datetime

class NLPClassifier:
    def __init__(self, model_path='ai_engine/saved_models/nlp_classifier.pkl'):
        self.model_path = model_path
        self.model = None
        
        # Mapping poli_id ke nama poli dan dokter
        self.label_map = {
            0: {"poli_name": "Umum", "doctor": "Dr. Budi"},
            1: {"poli_name": "Saraf", "doctor": "Dr. Siti"},
            2: {"poli_name": "Pencernaan", "doctor": "Dr. Andi"},
            3: {"poli_name": "Kebidanan", "doctor": "Bidan Yuli"},
            4: {"poli_name": "Tropis", "doctor": "Dr. Santoso"},
            5: {"poli_name": "Gizi", "doctor": "Ahli Gizi Rita"}
        }

        if os.path.exists(model_path):
            try:
                with open(model_path, 'rb') as f:
                    self.model = pickle.load(f)
            except Exception as e:
                print(f"Failed to load model: {e}")
                self.model = None

    def preprocess(self, text: str) -> str:
        # Lowercase
        t = str(text).lower()
        # Hapus karakter non-alfanumerik kecuali spasi
        t = re.sub(r'[^a-z0-9\s]', ' ', t)
        
        # Normalisasi singkatan medis / typo Indonesia
        replacements = {
            r'\bsakit perut\b': 'nyeri abdomen',
            r'\bpanas\b': 'demam',
            r'\bmules\b': 'kram perut',
            r'\bbatuk2\b': 'batuk',
            r'\bga bisa tidur\b': 'insomnia',
            r'\bsusah napas\b': 'sesak napas',
            r'\bmencret\b': 'diare',
            r'\bbab cair\b': 'diare',
            r'\bmuter muter\b': 'vertigo',
            r'\bberdarah\b': 'pendarahan',
            r'\bpusing banget\b': 'sakit kepala berat',
            r'\bga enak badan\b': 'lemas'
        }
        for pattern, repl in replacements.items():
            t = re.sub(pattern, repl, t)
            
        # Pisahkan dengan spasi, return sebagai string bersih
        t = ' '.join(t.split())
        return t

    def predict_poli(self, text: str) -> dict:
        clean_text = self.preprocess(text)
        
        if self.model is not None:
            # Predict probability
            probs = self.model.predict_proba([clean_text])[0]
            pred_id = int(self.model.predict([clean_text])[0])
            confidence = float(probs[pred_id])
            
            info = self.label_map.get(pred_id, {"poli_name": "Unknown", "doctor": "Unknown"})
            
            return {
                "poli_id": pred_id,
                "poli_name": info["poli_name"],
                "confidence": confidence,
                "doctor": info["doctor"]
            }
        else:
            # Fallback jika model belum dilatih (simulasi random atau default)
            return {
                "poli_id": 0,
                "poli_name": "Umum",
                "confidence": 0.5,
                "doctor": "Dr. Budi"
            }

    def train(self, training_data: list) -> dict:
        # Input format: [{"text": "...", "poli_id": 2, "poli_name": "Saraf"}, ...]
        texts = [self.preprocess(d["text"]) for d in training_data]
        y = [d["poli_id"] for d in training_data]
        
        # Pipeline: TfidfVectorizer + SVC
        pipeline = Pipeline([
            ('tfidf', TfidfVectorizer(ngram_range=(1,2), max_features=5000, sublinear_tf=True)),
            ('clf', SVC(kernel='rbf', C=1.0, probability=True, random_state=42))
        ])
        
        # StratifiedKFold
        skf = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)
        accuracies = []
        for train_index, test_index in skf.split(texts, y):
            X_train = [texts[i] for i in train_index]
            y_train = [y[i] for i in train_index]
            X_test = [texts[i] for i in test_index]
            y_test = [y[i] for i in test_index]
            
            pipeline.fit(X_train, y_train)
            preds = pipeline.predict(X_test)
            accuracies.append(accuracy_score(y_test, preds))
            
        avg_accuracy = sum(accuracies) / len(accuracies)
        
        # Train final model on all data
        pipeline.fit(texts, y)
        self.model = pipeline
        
        # Save model
        os.makedirs(os.path.dirname(self.model_path), exist_ok=True)
        with open(self.model_path, 'wb') as f:
            pickle.dump(self.model, f)
            
        # Save vectorizer (if needed separately, though it's in the pipeline)
        vectorizer_path = self.model_path.replace('nlp_classifier.pkl', 'vectorizer.pkl')
        with open(vectorizer_path, 'wb') as f:
            pickle.dump(pipeline.named_steps['tfidf'], f)
            
        return {
            "accuracy": avg_accuracy,
            "f1": f1_score(y, pipeline.predict(texts), average='weighted'),
            "report": classification_report(y, pipeline.predict(texts))
        }
