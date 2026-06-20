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
                
    # Tambahkan feedback data positif sebagai augmented training data
    if os.path.exists(feedback_path):
        # Baca interaction logs untuk cross-reference message_id
        interaction_map = {}
        interaction_log_path = 'data/interaction_logs.jsonl'
        if os.path.exists(interaction_log_path):
            with open(interaction_log_path, 'r', encoding='utf-8') as f:
                for line in f:
                    if line.strip():
                        try:
                            log_entry = json.loads(line)
                            if 'message_id' in log_entry:
                                interaction_map[log_entry['message_id']] = log_entry
                        except:
                            pass
        
        with open(feedback_path, 'r', encoding='utf-8') as f:
            for line in f:
                if line.strip():
                    try:
                        fb = json.loads(line)
                        # Hanya ambil feedback rating = 1 (koreksi positif / user setuju)
                        if fb.get('rating') == 1 and fb.get('message_id') in interaction_map:
                            log_data = interaction_map[fb['message_id']]
                            input_text = fb.get('original_input', log_data.get('input_text', ''))
                            # Ekstrak poli_id dari parameter_2 jika tersedia
                            param2 = log_data.get('parameter_2', {})
                            nlp_cls = param2.get('nlp_classification', {})
                            poli_id = nlp_cls.get('poli_id', 0)
                            
                            if input_text:
                                training_data.append({
                                    'text': input_text,
                                    'poli_id': poli_id,
                                    'poli_name': nlp_cls.get('poli_name', 'Umum'),
                                    'source': 'feedback_rlhf'
                                })
                    except Exception as e:
                        print(f"Warning: Error processing feedback line: {e}")
                    
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
