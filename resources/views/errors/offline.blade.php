<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koneksi Terputus - SI Puskesmas & Klinik</title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --secondary: #0f172a;
            --bg-light: #f8fafc;
            --font-display: 'Outfit', sans-serif;
            --font-sans: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-light);
            color: #334155;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .offline-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 20px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1);
            max-width: 480px;
            width: 100%;
            padding: 3rem 2rem;
            text-align: center;
            animation: fadeIn 0.5s ease-out forwards;
        }

        .offline-icon-wrapper {
            width: 80px;
            height: 80px;
            background-color: #fef2f2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2.2rem;
            box-shadow: 0 8px 16px rgba(239, 68, 68, 0.1);
        }

        h2 {
            font-family: var(--font-display);
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 1rem;
        }

        p {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #64748b;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-family: var(--font-display);
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            transition: all 0.25s ease;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .server-status {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 2rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="offline-card">
        <div class="offline-icon-wrapper">
            <i class="fa-solid fa-wifi-slash"></i>
        </div>
        <h2>Koneksi Terputus</h2>
        <p>
            Aplikasi tidak dapat terhubung ke server local Laragon Anda. Pastikan Laragon telah dinyalakan di komputer Anda dan server Apache/MySQL Anda telah aktif.
        </p>
        
        <button onclick="window.location.reload()" class="btn btn-primary w-100 text-white shadow-sm">
            <i class="fa-solid fa-rotate-right me-2"></i> Coba Reconnect / Segarkan
        </button>

        <div class="server-status">
            <i class="fa-solid fa-circle-nodes me-1"></i> Mode Offline Terdeteksi (SI Puskesmas PWA)
        </div>
    </div>

</body>
</html>
