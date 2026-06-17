from flask import Flask, request, jsonify
import logging
import time
import json
import pickle
import torch
from stable_baselines3 import PPO
from models.kbot_intelligence import KBotIntelligence
from models.nlp_classifier import NLPClassifier
from models.severity_scorer import SeverityScorer
from models.symptom_extractor import SymptomExtractor
from flask_cors import CORS
import os
import uuid

# Initialize Flask app
app = Flask(__name__)
CORS(app, origins=["http://localhost:5173", "http://127.0.0.1:5173", "http://localhost:8000"])

# Configure Logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Load models safely (try-except)
ml_model = None
try:
    with open('ai_engine/models/saved_ml_model.pkl', 'rb') as f:
        ml_model = pickle.load(f)
    logging.info("ML model loaded successfully.")
except Exception as e:
    logging.warning(f"Failed to load ML model: {e}")

# (For DL and RL, we would normally instantiate the network/agent first, 
# but for the API server boilerplate we assume paths)
dl_model = None  # To be implemented with TextClassificationNet
rl_agent = None  # To be implemented with PPO.load
kbot_ai = KBotIntelligence()
nlp_clf = NLPClassifier(model_path='ai_engine/saved_models/nlp_classifier.pkl')
severity_scorer = SeverityScorer()
symptom_extractor = SymptomExtractor()

@app.route('/predict/surge', methods=['POST'])
def predict_surge():
    """Predicts if there will be a surge based on time features."""
    if ml_model is None:
        return jsonify({'error': 'ML model not loaded.'}), 500
    
    data = request.json
    try:
        # Expected input: {"poli_id": 1, "day_of_week": 2, "hour": 10}
        poli_id = data.get('poli_id')
        day_of_week = data.get('day_of_week')
        hour = data.get('hour')
        
        # Predict
        prediction = ml_model.predict([[poli_id, day_of_week, hour]])
        is_surge = bool(prediction[0])
        
        return jsonify({
            'status': 'success',
            'is_surge': is_surge,
            'message': 'Surge detected' if is_surge else 'Normal traffic expected'
        }), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 400

@app.route('/predict/complaint', methods=['POST'])
def predict_complaint():
    """Classifies medical complaint text (NER & Triage)."""
    data = request.json
    text = data.get('text', '')
    
    if not text:
        return jsonify({'error': 'Text is empty'}), 400
        
    # 1. Out of Domain Check
    is_ood = severity_scorer.is_out_of_domain(text)
    if is_ood:
        return jsonify({
            'status': 'success',
            'is_out_of_domain': True,
            'is_emergency': False,
            'message': 'Input tidak dikenali sebagai keluhan medis yang valid.'
        }), 200
        
    # 2. Extract Symptoms (NER)
    extracted_entities = symptom_extractor.extract(text)
    
    # 3. Predict Poli
    poli_result = nlp_clf.predict_poli(text)
    confidence = poli_result.get("confidence", 0.85)
    
    # Mock poli id mapping
    poli_name_raw = poli_result.get("doctor", "Umum")
    predicted_poli_id = 1
    if "Gigi" in poli_name_raw: predicted_poli_id = 2
    elif "Kebidanan" in poli_name_raw: predicted_poli_id = 3
    elif "Saraf" in poli_name_raw: predicted_poli_id = 4
    elif "Pencernaan" in poli_name_raw: predicted_poli_id = 5
    elif "Gizi" in poli_name_raw: predicted_poli_id = 6
    
    # 4. Severity & Emergency Check
    severity_result = severity_scorer.score(text, confidence)
    
    return jsonify({
        'status': 'success',
        'is_out_of_domain': False,
        'is_emergency': severity_result.get('is_emergency', False),
        'extracted_entities': extracted_entities,
        'predicted_poli_id': predicted_poli_id, 
        'confidence': confidence,
        'cdc_triage': severity_result.get('cdc_triage', 'GREEN TAG'),
        'action': severity_result.get('action', '')
    }), 200

@app.route('/optimize/queue', methods=['POST'])
def optimize_queue():
    """Optimizes queue based on current state (Boilerplate)."""
    data = request.json
    queue_length = data.get('queue_length', 0)
    
    # Normally we would query rl_agent
    # action, _states = rl_agent.predict([queue_length])
    
    return jsonify({
        'status': 'success',
        'recommended_action': 1, # 1: Open new counter (mock action)
        'message': 'Recommended to open new counter' if queue_length > 10 else 'Queue is normal'
    }), 200

@app.route('/kbot/analyze', methods=['POST'])
def analyze_kbot():
    """Endpoint for Enterprise K-Bot NLP & Math Analysis."""
    data = request.json
    message = data.get('message', '')
    
    if not message:
        return jsonify({'error': 'Message cannot be empty'}), 400
        
    try:
        message_id = str(uuid.uuid4())
        # Predict NLP (IndoBERT/SVM fallback)
        poli_result = nlp_clf.predict_poli(message)
        
        # Panggil modul intelijen kBot
        parameter_1, parameter_2 = kbot_ai.process_request(message, nlp_result=poli_result)
        
        # Insert nlp_classification into parameter_2
        parameter_2["nlp_classification"] = poli_result
        
        # Simpan log interaksi untuk bahan Evaluasi Real-Time (Alignment Research)
        log_entry = {
            "message_id": message_id,
            "timestamp": time.time(),
            "input_text": message,
            "parameter_1": parameter_1,
            "parameter_2": parameter_2
        }
        
        os.makedirs('ai_engine/data', exist_ok=True)
        with open('ai_engine/data/interaction_logs.jsonl', 'a') as f:
            f.write(json.dumps(log_entry) + '\n')
            
        return jsonify({
            'status': 'success',
            'message_id': message_id,
            'parameter_1': parameter_1,
            'parameter_2': parameter_2
        }), 200
    except Exception as e:
        logging.error(f"Error in /kbot/analyze: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/kbot/feedback', methods=['POST'])
def kbot_feedback():
    data = request.json
    message_id = data.get('message_id')
    rating = data.get('rating')
    original_input = data.get('original_input', '')
    
    if rating not in [0, 1]:
        return jsonify({"error": "Rating must be 0 or 1"}), 400
        
    log_entry = {
        "timestamp": time.time(),
        "message_id": message_id,
        "rating": rating,
        "original_input": original_input
    }
    
    os.makedirs('ai_engine/data', exist_ok=True)
    with open('ai_engine/data/feedback_labels.jsonl', 'a') as f:
        f.write(json.dumps(log_entry) + '\n')
        
    return jsonify({"status": "success", "message": "Feedback recorded"}), 200

@app.route('/kbot/train', methods=['GET'])
def kbot_train():
    if request.remote_addr not in ['127.0.0.1', 'localhost']:
        return jsonify({"error": "Forbidden"}), 403
        
    try:
        from training.train_classifier import train_main
        result = train_main()
        if result is None:
            return jsonify({"error": "Training failed or insufficient data"}), 500
            
        return jsonify({
            "status": "success", 
            "accuracy": result["accuracy"], 
            "n_samples": 80, # Hardcoded for now based on PRD
            "version": "1.0.0"
        }), 200
    except Exception as e:
        logging.error(f"Error in /kbot/train: {e}")
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    logging.info("Starting AI Engine API Server on port 5000...")
    app.run(host='0.0.0.0', port=5000, debug=False)
