<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Digital - BPSDM</title>
    <link rel="icon" type="image/png" href="assets/img/logo-presensi.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
    /* CSS Kustom untuk Landing Page */
    html { scroll-behavior: smooth; }
    body { font-family: 'Poppins', sans-serif; }
    
    /* Navbar Styling */
    .navbar {
        transition: background-color 0.4s ease, padding 0.4s ease;
    }
    
    /* Style Awal (Saat di Atas Hero Section) */
    .navbar .navbar-brand span,
    .navbar .nav-link {
        transition: color 0.4s ease;
        color: rgba(255, 255, 255, 0.8); /* Putih transparan */
    }
    .navbar .nav-link:hover, .navbar .navbar-brand:hover span {
        color: #fff; /* Putih solid */
    }
    
    /* --- PERBAIKAN UTAMA DI SINI --- */
    /* Style Awal untuk Tombol Login */
    .navbar .btn-login {
        background-color: #fff; /* Latar belakang putih */
        color: #0d6efd; /* Tulisan biru */
        border: 1px solid #fff;
        font-weight: 500;
        transition: all 0.4s ease;
    }
    .navbar .btn-login:hover {
        background-color: rgba(255, 255, 255, 0.85); /* Sedikit transparan saat hover */
        color: #0d6efd;
    }

    /* Kelas Saat Navbar Di-scroll */
    .navbar-scrolled {
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .navbar-scrolled .navbar-brand span,
    .navbar-scrolled .nav-link {
        color: #343a40; /* Abu-abu gelap */
    }
    .navbar-scrolled .nav-link:hover, .navbar-scrolled .navbar-brand:hover span {
        color: #0d6efd; /* Biru */
    }
    
    /* --- PERBAIKAN UTAMA DI SINI --- */
    /* Style Tombol Login Setelah Scroll */
    .navbar-scrolled .btn-login {
        background-color: #0d6efd; /* Latar belakang biru */
        border-color: #0d6efd;
        color: white; /* Tulisan putih */
    }
    .navbar-scrolled .btn-login:hover {
        opacity: 0.9;
    }
        section { padding: 80px 0; overflow: hidden; }
        .hero {
            background: linear-gradient(to right, #0d6efd, #0dcaf0);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .hero h1 { font-size: 3.5rem; font-weight: 700; }
        .hero p { font-size: 1.25rem; opacity: 0.9; }
        .hero img { max-width: 80%; height: auto; }
        .section-title { margin-bottom: 50px; }
        .section-title h2 { font-weight: 700; }
        .feature-box, .contact-box {
            padding: 30px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            height: 100%;
        }
        .feature-box i, .contact-box i {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        #fitur ul { list-style: none; padding: 0; }
        #fitur ul li { padding: 5px 0; }
        #fitur ul li i { color: #198754; margin-right: 10px; }
        footer { background-color: #f8f9fa; }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navbar / Header -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
            <img src="assets/img/logo-presensi.png" alt="Logo" height="30" class="me-2"> 
            <span>Presensi Digital</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#hero">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                <li class="nav-item"><a class="nav-link" href="#fitur">Fitur & Manfaat</a></li>
                <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                <li class="nav-item ms-lg-3"><a class="btn btn-primary" href="login.php">Login Panel</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page 1: Hero Section -->
<section id="hero" class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1>Presensi Kegiatan Digital</h1>
                <p class="lead">Cukup dengan satu klik, catat kehadiran di setiap sesi kegiatan pelatihan. Proses absensi menjadi lebih mudah, cepat, dan terdokumentasi dengan baik.</p>
            </div>
            <div class="col-lg-6 text-center">
                <img src="assets/img/logo-presensi.png" alt="Logo Presensi Digital" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Page 2: Tentang -->
<section id="tentang">
    <div class="container">
        <div class="section-title text-center">
            <h2>Apa Itu Presensi Digital?</h2>
            <p>Platform digital untuk memodernisasi proses pencatatan kehadiran, memastikan data yang akurat dan dapat dipertanggungjawabkan.</p>
        </div>
        <div class="row">
            <div class="col-md-4"><div class="feature-box text-center"><i class="bi bi-link-45deg"></i><h5>Presensi via Link</h5><p>Peserta melakukan konfirmasi kehadiran secara instan melalui link unik yang dibagikan.</p></div></div>
            <div class="col-md-4"><div class="feature-box text-center"><i class="bi bi-file-earmark-spreadsheet"></i><h5>Rekapitulasi Otomatis</h5><p>Data kehadiran direkapitulasi secara *real-time*, menghilangkan entri data manual.</p></div></div>
            <div class="col-md-4"><div class="feature-box text-center"><i class="bi bi-shield-check"></i><h5>Validitas Data Terjamin</h5><p>Setiap entri absensi tercatat dengan aman dan dapat diaudit, memastikan integritas data.</p></div></div>
        </div>
    </div>
</section>

<!-- Page 3: Fitur & Manfaat -->
<section id="fitur" class="bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="section-title"><h3>Fitur Sistem</h3></div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Formulir Kustom (upload, tanda tangan, dll).</li>
                    <li><i class="bi bi-check-circle-fill"></i> Tampilan responsif di semua perangkat.</li>
                    <li><i class="bi bi-check-circle-fill"></i> Ekspor laporan ke format Excel.</li>
                    <li><i class="bi bi-check-circle-fill"></i> Panel admin yang aman untuk pengelolaan.</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="section-title"><h3>Manfaat Digitalisasi</h3></div>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Efisiensi waktu proses absensi manual.</li>
                    <li><i class="bi bi-check-circle-fill"></i> Akurasi data tinggi, mengurangi *human error*.</li>
                    <li><i class="bi bi-check-circle-fill"></i> Ramah lingkungan (*paperless*).</li>
                    <li><i class="bi bi-check-circle-fill"></i> Data terpusat, aman, dan dapat diakses *real-time*.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Page 4: Kontak -->
<section id="kontak">
    <div class="container">
        <div class="section-title text-center"><h2>Kontak</h2></div>
        <div class="row text-center">
            <div class="col-md-4"><div class="contact-box"><i class="bi bi-geo-alt-fill"></i><h5>Alamat</h5><p>Jl. Kolonel Masturi No.11, KM 3,5, Cipageran, Cimahi</p></div></div>
            <div class="col-md-4"><div class="contact-box"><i class="bi bi-telephone-fill"></i><h5>Telepon</h5><p>022-6649471</p></div></div>
            <div class="col-md-4"><div class="contact-box"><i class="bi bi-envelope-fill"></i><h5>Email</h5><p>bpsdm@jabarprov.go.id</p></div></div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="py-4">
    <div class="container text-center text-muted">
         <p class="mb-0">&copy;Sem <?php echo date('Y'); ?> Presensi Digital. Hak Cipta Dilindungi.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript untuk mengubah background navbar saat scroll
    const navbar = document.querySelector('.navbar');
    window.onscroll = () => {
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    };
</script>
</body>
</html>