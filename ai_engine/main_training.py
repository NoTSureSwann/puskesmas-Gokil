# AI Engine Daemon for background ML/DL/RL model training jobs
import time
import schedule
import mysql.connector
from mysql.connector import Error
import logging

from models.machine_learning import train_ml_model
from models.deep_learning import train_dl_model
from models.reinforcement_learning import train_rl_agent

# Configure Logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

def get_db_connection():
    try:
        connection = mysql.connector.connect(
            host='127.0.0.1',
            database='puskesmas_johar_baru',
            user='root',
            password=''
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

if __name__ == "__main__":
    logging.info("AI Engine Started. Scheduling training jobs...")
    
    # Schedule Machine Learning training every day at midnight (example)
    schedule.every().day.at("00:00").do(job_ml)
    
    # Schedule Deep Learning training every week (example)
    schedule.every().sunday.at("02:00").do(job_dl)
    
    # Schedule Reinforcement Learning optimization every hour (example)
    schedule.every(1).hours.do(job_rl)
    
    # Run once immediately for testing/demonstration
    job_ml()
    job_dl()
    job_rl()

    # Keep the script running
    while True:
        schedule.run_pending()
        time.sleep(60)
