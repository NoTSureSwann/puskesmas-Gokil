import logging
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score
import pickle

def train_ml_model(db_connection):
    """
    Supervised Learning: Memprediksi kemungkinan lonjakan pasien 
    berdasarkan data historis pendaftaran (contoh implementasi).
    """
    logging.info("[ML] Mengambil data historis pendaftaran dari database...")
    
    try:
        query = "SELECT id, poli_id, status, created_at FROM kunjungan"
        df = pd.read_sql(query, con=db_connection)
        
        if df.empty or len(df) < 50:
            logging.info("[ML] Data belum cukup untuk training Machine Learning (butuh > 50 baris).")
            return
            
        logging.info("[ML] Memproses fitur...")
        # Ekstraksi fitur waktu
        df['created_at'] = pd.to_datetime(df['created_at'])
        df['day_of_week'] = df['created_at'].dt.dayofweek
        df['hour'] = df['created_at'].dt.hour
        
        # Contoh: Memprediksi apakah poli akan 'Penuh' (High Traffic) atau 'Normal'
        # Di sini kita mock target variabel untuk keperluan boilerplate
        X = df[['poli_id', 'day_of_week', 'hour']]
        y = (df['hour'] >= 8) & (df['hour'] <= 12) # Jam sibuk = 1, sisanya 0
        
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        
        logging.info("[ML] Memulai proses training RandomForest...")
        model = RandomForestClassifier(n_estimators=100, random_state=42)
        model.fit(X_train, y_train)
        
        predictions = model.predict(X_test)
        acc = accuracy_score(y_test, predictions)
        
        logging.info(f"[ML] Training selesai. Akurasi Model: {acc:.2f}")
        
        # Simpan model
        with open('ai_engine/models/saved_ml_model.pkl', 'wb') as f:
            pickle.dump(model, f)
        logging.info("[ML] Model ML berhasil disimpan ke disk.")
        
    except Exception as e:
        logging.error(f"[ML] Terjadi kesalahan: {e}")
