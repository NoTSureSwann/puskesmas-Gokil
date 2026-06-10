import sys
import os
import json

# Pastikan import dari parent folder bekerja (ai_engine)
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))

from models.nlp_classifier import NLPClassifier

def train_main():
    data_path = 'data/training_data.jsonl'
    feedback_path = 'data/feedback_labels.jsonl'
    
    # Set cwd ke ai_engine agar relative paths work
    os.chdir(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
    
    if not os.path.exists(data_path):
        print(f"Error: {data_path} not found.")
        return None
        
    training_data = []
    with open(data_path, 'r', encoding='utf-8') as f:
        for line in f:
            if line.strip():
                training_data.append(json.loads(line))
                
    # Tambahkan feedback data jika ada
    if os.path.exists(feedback_path):
        with open(feedback_path, 'r', encoding='utf-8') as f:
            for line in f:
                if line.strip():
                    fb = json.loads(line)
                    # Hanya ambil feedback rating = 1 (koreksi positif)
                    # Atau ini akan dikembangkan lebih lanjut di feedback_collector
                    pass
                    
    if len(training_data) < 20:
        print("WARNING: Data latih < 20. Hentikan.")
        return None
        
    print(f"Training on {len(training_data)} samples...")
    
    classifier = NLPClassifier(model_path='saved_models/nlp_classifier.pkl')
    result = classifier.train(training_data)
    
    print(f"Model saved. Accuracy: {result['accuracy']*100:.2f}%")
    print("Report:")
    print(result['report'])
    
    os.makedirs('saved_models', exist_ok=True)
    with open('saved_models/eval_report.txt', 'w', encoding='utf-8') as f:
        f.write(result['report'])
        
    return result

if __name__ == '__main__':
    train_main()
