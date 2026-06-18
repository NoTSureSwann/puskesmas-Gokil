import sys
import os
import json
import csv

# Mengarah ke direktori Laravel root tempat log disimpan
LARAVEL_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
LOG_DIR = os.path.join(LARAVEL_ROOT, 'storage', 'app', 'ai_engine_data')

def export_dpo_dataset(output_path='data/dpo_rlhf_dataset.jsonl'):
    feedback_path = os.path.join(LOG_DIR, 'feedback_labels.jsonl')
    logs_path = os.path.join(LOG_DIR, 'interaction_logs.jsonl')
    
    # Change dir to script directory for saving output
    os.chdir(os.path.abspath(os.path.dirname(__file__)))
    
    if not os.path.exists(feedback_path) or not os.path.exists(logs_path):
        print("Log files not found. Ensure KBot has been used and rated.")
        return
        
    # 1. Baca log interaction (Prompt & Response Groq)
    interactions = {}
    with open(logs_path, 'r', encoding='utf-8') as f:
        for line in f:
            if line.strip():
                try:
                    data = json.loads(line)
                    if 'message_id' in data:
                        interactions[data['message_id']] = data
                except:
                    pass
                    
    # 2. Baca feedback ratings
    dpo_dataset = []
    
    with open(feedback_path, 'r', encoding='utf-8') as f:
        for line in f:
            if line.strip():
                try:
                    fb = json.loads(line)
                    msg_id = fb.get('message_id')
                    
                    if msg_id in interactions:
                        interaction = interactions[msg_id]
                        # Ekstrak prompt user terakhir
                        user_prompt = ""
                        for msg in interaction.get('prompt', []):
                            if msg.get('role') == 'user':
                                user_prompt = msg.get('content', '')
                        
                        # Ekstrak response JSON utuh dari model Llama
                        model_response = json.dumps(interaction.get('response', {}), ensure_ascii=False)
                        
                        # Untuk DPO, kita asumsikan:
                        # Jika rating = 1 (Bagus), maka ini adalah 'chosen'
                        # Jika rating = 0 (Buruk), maka ini adalah 'rejected'
                        
                        # Kita membutuhkan format: prompt, chosen, rejected
                        # Karena kita hanya punya salah satu per interaksi, kita simpan sebagai single response
                        # Nantinya saat training, script HF TRL bisa memfilter atau kita buat sintesis.
                        
                        dpo_dataset.append({
                            "prompt": user_prompt,
                            "response": model_response,
                            "rating": fb.get('rating'),
                            "is_chosen": fb.get('rating') == 1
                        })
                except Exception as e:
                    print("Error parsing line:", e)
                    
    # 3. Gabungkan dan export
    if not dpo_dataset:
        print("Tidak ada dataset DPO/RLHF yang berhasil di-ekstrak.")
        return
        
    with open(output_path, 'w', encoding='utf-8') as f:
        for item in dpo_dataset:
            f.write(json.dumps(item, ensure_ascii=False) + "\n")
            
    print(f"Exported {len(dpo_dataset)} RLHF interactions to {output_path}")

if __name__ == '__main__':
    export_dpo_dataset()
