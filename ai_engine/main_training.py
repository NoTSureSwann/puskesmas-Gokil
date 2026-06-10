# AI Engine Daemon for background ML/DL/RL model training jobs
import time
import os
import schedule
import mysql.connector
from mysql.connector import Error
import logging

from models.machine_learning import train_ml_model
from models.deep_learning import train_dl_model
from models.reinforcement_learning import train_rl_agent
from models.alignment_research import run_alignment_evaluation_loop
from models.kbot_intelligence import KBotIntelligence

# Configure Logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

def load_env_vars():
    """
    Membaca berkas .env dari parent directory secara real-time
    agar kredensial database selalu tersinkronisasi.
    """
    env_vars = {}
    try:
        current_dir = os.path.dirname(os.path.abspath(__file__))
        env_path = os.path.join(current_dir, '..', '.env')
        if os.path.exists(env_path):
            with open(env_path, 'r') as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#') and '=' in line:
                        key, value = line.split('=', 1)
                        env_vars[key.strip()] = value.strip('"\'')
        else:
            logging.warning(f".env tidak ditemukan di {env_path}, menggunakan fallback.")
    except Exception as e:
        logging.error(f"Gagal membaca .env: {e}")
    return env_vars

def get_db_connection():
    env = load_env_vars()
    try:
        connection = mysql.connector.connect(
            host=env.get('DB_HOST', '127.0.0.1'),
            port=int(env.get('DB_PORT', 3306)),
            database=env.get('DB_DATABASE', 'puskesmas_johar_baru'),
            user=env.get('DB_USERNAME', 'root'),
            password=env.get('DB_PASSWORD', '')
        )
        if connection.is_connected():
            return connection
    except Error as e:
        logging.error(f"Error connecting to MySQL: {e}")
    return None

def job_ml():
    logging.info("Starting Machine Learning job...")
    conn = get_db_connection()
    if conn:
        train_ml_model(conn)
        conn.close()

def job_dl():
    logging.info("Starting Deep Learning job...")
    conn = get_db_connection()
    if conn:
        train_dl_model(conn)
        conn.close()

def job_rl():
    logging.info("Starting Reinforcement Learning job...")
    conn = get_db_connection()
    if conn:
        train_rl_agent(conn)
        conn.close()

def job_alignment_eval():
    logging.info("Starting Real-Time Alignment Evaluation Loop...")
    try:
        # Panggil KBot instance sebagai mock testbed
        kbot_instance = KBotIntelligence()
        report = run_alignment_evaluation_loop(kbot_instance)
        logging.info(f"Alignment Report: {report}")
    except Exception as e:
        logging.error(f"Alignment Eval Error: {e}")

if __name__ == "__main__":
    logging.info("AI Engine Started. Scheduling training jobs...")
    
    # Schedule Machine Learning training every day at midnight (example)
    schedule.every().day.at("00:00").do(job_ml)
    
    # Schedule Deep Learning training every week (example)
    schedule.every().sunday.at("02:00").do(job_dl)
    
    # Schedule Reinforcement Learning optimization every hour (example)
    schedule.every(1).hours.do(job_rl)
    
    # Schedule Real-Time Alignment Evaluation every 10 minutes (fast iteration)
    schedule.every(10).minutes.do(job_alignment_eval)
    
    # Run once immediately for testing/demonstration
    job_ml()
    job_dl()
    job_rl()
    job_alignment_eval()

    # Keep the script running
    while True:
        schedule.run_pending()
        time.sleep(60)
