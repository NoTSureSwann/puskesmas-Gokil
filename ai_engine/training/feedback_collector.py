import sys
import os
import json
import csv

sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))

def collect_negative_feedback():
    feedback_path = 'data/feedback_labels.jsonl'
    logs_path = 'data/interaction_logs.jsonl'
    
    os.chdir(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
    
    if not os.path.exists(feedback_path) or not os.path.exists(logs_path):
        return []
        
    negatives = []
    
    # Baca log interaction
    interactions = {}
    with open(logs_path, 'r', encoding='utf-8') as f:
        for line in f:
            if line.strip():
                try:
                    data = json.loads(line)
                    interactions[data.get('timestamp')] = data
                except:
                    pass
                    
    # Baca feedback
    with open(feedback_path, 'r', encoding='utf-8') as f:
        for line in f:
            if line.strip():
                try:
                    fb = json.loads(line)
                    if fb.get('rating') == 0:
                        negatives.append(fb)
                except:
                    pass
                    
    # Gabungkan
    results = []
    for neg in negatives:
        results.append({
            "text": neg.get("original_input", ""),
            "needs_review": True
        })
        
    return results

def export_for_relabeling(output_path='data/relabel_queue.csv'):
    os.chdir(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
    negatives = collect_negative_feedback()
    
    if not negatives:
        print("Tidak ada feedback negatif untuk di-relabel.")
        return
        
    with open(output_path, 'w', newline='', encoding='utf-8') as csvfile:
        fieldnames = ['text', 'correct_poli']
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        
        writer.writeheader()
        for neg in negatives:
            writer.writerow({'text': neg['text'], 'correct_poli': ''})
            
    print(f"Exported {len(negatives)} rows to {output_path}")

if __name__ == '__main__':
    export_for_relabeling()
