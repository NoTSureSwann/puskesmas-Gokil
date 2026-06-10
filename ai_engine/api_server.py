from flask import Flask, request, jsonify
import logging
import time
import json
import pickle
import torch
from stable_baselines3 import PPO
from models.kbot_intelligence import KBotIntelligence

# Initialize Flask app
app = Flask(__name__)

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
    """Classifies medical complaint text (Boilerplate)."""
    data = request.json
    text = data.get('text', '')
    
    # Normally we would use dl_model here
    # For now, we return a mock response
    return jsonify({
        'status': 'success',
        'predicted_poli_id': 1, # Mock output
        'confidence': 0.85
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
        # Panggil modul intelijen kBot
        parameter_1, parameter_2 = kbot_ai.process_request(message)
        
        # Simpan log interaksi untuk bahan Evaluasi Real-Time (Alignment Research)
        log_entry = {
            "timestamp": time.time(),
            "input_text": message,
            "parameter_1": parameter_1,
            "parameter_2": parameter_2
        }
        
        import os
        os.makedirs('ai_engine/data', exist_ok=True)
        with open('ai_engine/data/interaction_logs.jsonl', 'a') as f:
            f.write(json.dumps(log_entry) + '\n')
            
        return jsonify({
            'status': 'success',
            'parameter_1': parameter_1,
            'parameter_2': parameter_2
        }), 200
    except Exception as e:
        logging.error(f"Error in /kbot/analyze: {e}")
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    logging.info("Starting AI Engine API Server on port 5000...")
    app.run(host='0.0.0.0', port=5000, debug=False)
