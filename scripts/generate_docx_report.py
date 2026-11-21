import os
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, hex_color):
    shading_elm = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
    cell._tc.get_or_add_tcPr().append(shading_elm)

def set_cell_margins(cell, top=120, bottom=120, left=140, right=140):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    tcPr.append(tcMar)

def set_table_borders(table, color="D1D5DB", sz="4", val="single"):
    tblPr = table._tbl.tblPr
    borders = parse_xml(
        f'<w:tblBorders {nsdecls("w")}>'
        f'<w:top w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
        f'<w:bottom w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
        f'<w:left w:val="none"/>'
        f'<w:right w:val="none"/>'
        f'<w:insideH w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
        f'<w:insideV w:val="none"/>'
        f'</w:tblBorders>'
    )
    tblPr.append(borders)

def make_callout_box(doc, text, title="CATATAN PENGUJIAN"):
    tbl = doc.add_table(rows=1, cols=1)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = tbl.cell(0, 0)
    set_cell_background(cell, "F0F4F8")
    set_cell_margins(cell, top=140, bottom=140, left=200, right=200)
    
    tcPr = cell._tc.get_or_add_tcPr()
    borders = parse_xml(
        f'<w:tcBorders {nsdecls("w")}>'
        f'<w:left w:val="single" w:sz="24" w:space="0" w:color="1E3A8A"/>'
        f'<w:top w:val="none"/>'
        f'<w:right w:val="none"/>'
        f'<w:bottom w:val="none"/>'
        f'</w:tcBorders>'
    )
    tcPr.append(borders)
    
    p = cell.paragraphs[0]
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(2)
    run_t = p.add_run(f"[{title}] ")
    run_t.bold = True
    run_t.font.name = "Calibri"
    run_t.font.size = Pt(10)
    run_t.font.color.rgb = RGBColor(30, 58, 138)
    
    run_body = p.add_run(text)
    run_body.font.name = "Calibri"
    run_body.font.size = Pt(9.5)
    run_body.font.color.rgb = RGBColor(51, 65, 85)
    
    p_after = doc.add_paragraph()
    p_after.paragraph_format.space_after = Pt(4)

def format_table(table, col_widths, headers, data, alt_row=True):
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(table)
    
    # Format Header
    hdr_cells = table.rows[0].cells
    for i, title in enumerate(headers):
        hdr_cells[i].text = title
        set_cell_background(hdr_cells[i], "1E3A8A")
        set_cell_margins(hdr_cells[i], top=140, bottom=140, left=120, right=120)
        p = hdr_cells[i].paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_before = Pt(2)
        p.paragraph_format.space_after = Pt(2)
        for run in p.runs:
            run.font.bold = True
            run.font.name = "Calibri"
            run.font.size = Pt(9.5)
            run.font.color.rgb = RGBColor(255, 255, 255)
    
    # Repeat header row & cantSplit
    trPr = table.rows[0]._tr.get_or_add_trPr()
    trPr.append(parse_xml(f'<w:tblHeader {nsdecls("w")}/>'))
    trPr.append(parse_xml(f'<w:cantSplit {nsdecls("w")}/>'))
    
    # Data rows
    for row_idx, row_data in enumerate(data):
        row = table.rows[row_idx + 1]
        row_trPr = row._tr.get_or_add_trPr()
        row_trPr.append(parse_xml(f'<w:cantSplit {nsdecls("w")}/>'))
        
        bg_color = "F8FAFC" if (row_idx % 2 == 1 and alt_row) else "FFFFFF"
        for col_idx, cell_value in enumerate(row_data):
            cell = row.cells[col_idx]
            cell.text = cell_value
            set_cell_background(cell, bg_color)
            set_cell_margins(cell, top=100, bottom=100, left=120, right=120)
            cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
            p = cell.paragraphs[0]
            p.paragraph_format.space_before = Pt(2)
            p.paragraph_format.space_after = Pt(2)
            
            # Alignments
            if col_idx in [0, 4, 5]:
                p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            else:
                p.alignment = WD_ALIGN_PARAGRAPH.LEFT
                
            for run in p.runs:
                run.font.name = "Calibri"
                run.font.size = Pt(9)
                if col_idx == 0:
                    run.font.bold = True
                    run.font.color.rgb = RGBColor(30, 58, 138)
                elif col_idx == 4:
                    run.font.bold = True
                    if "Critical" in cell_value:
                        run.font.color.rgb = RGBColor(185, 28, 28) # Red
                    elif "High" in cell_value:
                        run.font.color.rgb = RGBColor(194, 65, 12) # Orange
                    else:
                        run.font.color.rgb = RGBColor(180, 83, 9) # Amber
                else:
                    run.font.color.rgb = RGBColor(51, 65, 85)
                    
    # Set Widths
    for row in table.rows:
        for idx, width in enumerate(col_widths):
            row.cells[idx].width = width

def build_document():
    doc = Document()
    
    # Page Setup
    sections = doc.sections
    for section in sections:
        section.top_margin = Inches(0.8)
        section.bottom_margin = Inches(0.8)
        section.left_margin = Inches(0.8)
        section.right_margin = Inches(0.8)
        
    # Set default style
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Calibri'
    normal_style.font.size = Pt(10)
    normal_style.font.color.rgb = RGBColor(30, 41, 59)
    
    # Header Banner Block
    title_p = doc.add_paragraph()
    title_p.paragraph_format.space_before = Pt(0)
    title_p.paragraph_format.space_after = Pt(2)
    run_badge = title_p.add_run("QUALITY ASSURANCE & ADVERSARIAL TESTING REPORT\n")
    run_badge.font.name = "Calibri"
    run_badge.font.size = Pt(9.5)
    run_badge.font.bold = True
    run_badge.font.color.rgb = RGBColor(37, 99, 235)
    
    run_title = title_p.add_run("Rancangan Skenario Negative Thinking (Destructive Testing)\nAplikasi Eduface — Sistem Presensi & Face Recognition")
    run_title.font.name = "Calibri"
    run_title.font.size = Pt(17)
    run_title.font.bold = True
    run_title.font.color.rgb = RGBColor(15, 23, 42)
    
    sub_p = doc.add_paragraph()
    sub_p.paragraph_format.space_before = Pt(2)
    sub_p.paragraph_format.space_after = Pt(12)
    run_sub = sub_p.add_run("Alokasi 4 Penguji (Masing-masing 2 Modul) untuk Validasi Keamanan, Ketahanan Logika Bisnis, dan Integritas Data")
    run_sub.font.name = "Calibri"
    run_sub.font.size = Pt(10.5)
    run_sub.font.italic = True
    run_sub.font.color.rgb = RGBColor(100, 116, 139)
    
    # Metadata Table
    meta_table = doc.add_table(rows=4, cols=2)
    meta_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    set_table_borders(meta_table, color="E2E8F0", sz="4")
    
    meta_info = [
        ("Nama Aplikasi / Versi", "Eduface (Laravel 10 / 11 + Face-API Recognition) - v1.0 Production"),
        ("Tipe Pengujian", "Negative Testing, Boundary Testing, Adversarial/Abuse Case Testing, RBAC Audit"),
        ("Alokasi Penguji", "4 Personel QA / Software Tester (Setiap Personel memegang 2 Modul Inti)"),
        ("Lingkungan Uji (Environment)", "Development / Staging Server (PHP 8.2, MySQL 8, Face Recognition Engine)")
    ]
    
    for i, (k, v) in enumerate(meta_info):
        r = meta_table.rows[i]
        c0, c1 = r.cells[0], r.cells[1]
        c0.text, c1.text = k, v
        set_cell_background(c0, "F1F5F9")
        set_cell_background(c1, "FFFFFF")
        set_cell_margins(c0, top=60, bottom=60, left=100, right=100)
        set_cell_margins(c1, top=60, bottom=60, left=100, right=100)
        c0.width = Inches(2.2)
        c1.width = Inches(4.7)
        c0.paragraphs[0].runs[0].font.bold = True
        c0.paragraphs[0].runs[0].font.size = Pt(9)
        c1.paragraphs[0].runs[0].font.size = Pt(9)
        
    p_div = doc.add_paragraph()
    p_div.paragraph_format.space_after = Pt(10)
    
    # BAB 1
    h1 = doc.add_heading(level=1)
    run_h1 = h1.add_run("1. Pendahuluan & Prinsip Pengujian Negatif")
    run_h1.font.name = "Calibri"
    run_h1.font.size = Pt(13)
    run_h1.font.bold = True
    run_h1.font.color.rgb = RGBColor(30, 58, 138)
    
    p_intro = doc.add_paragraph(
        "Negative Thinking dalam konteks Software Quality Assurance adalah metode pengujian yang berfokus pada "
        "asumsi terburuk (adversarial mindset): bagaimana jika pengguna bertindak tidak wajar, memanipulasi request jaringan, "
        "melakukan tindakan curang, atau memberikan input data ekstrem dan tidak valid. Tujuan utama dari pengujian ini bukan "
        "hanya mencari error biasa, melainkan memastikan sistem Eduface mampu merespons anomali secara anggun (graceful handling), "
        "mencegah kebocoran data (information leakage), menjaga konsistensi database, serta menolak eskalasi hak akses."
    )
    p_intro.paragraph_format.space_after = Pt(6)
    
    make_callout_box(
        doc,
        "Setiap skenario negatif dirancang spesifik dengan controller dan arsitektur aplikasi Eduface. "
        "Hasil pengujian dianggap LULUS (PASS) apabila sistem secara konsisten MENOLAK input jahat/abnormal dengan validasi yang jelas "
        "dan TIDAK menampilkan halaman error fatal (HTTP 500 / unhandled exception) maupun mengeksekusi payload.",
        "PRINSIP UTAMA KELULUSAN UJI"
    )
    
    # BAB 2
    h2 = doc.add_heading(level=1)
    run_h2 = h2.add_run("2. Matriks Pembagian Tugas Pengujian (4 Penguji)")
    run_h2.font.name = "Calibri"
    run_h2.font.size = Pt(13)
    run_h2.font.bold = True
    run_h2.font.color.rgb = RGBColor(30, 58, 138)
    
    p_matriks = doc.add_paragraph(
        "Seluruh modul aplikasi Eduface dipartisi secara proporsional kepada 4 orang penguji dengan spesialisasi fokus uji tertentu:"
    )
    p_matriks.paragraph_format.space_after = Pt(6)
    
    matrix_headers = ["Penguji", "Alokasi Modul 1", "Alokasi Modul 2", "Fokus Ancaman & Area Kritis"]
    matrix_widths = [Inches(1.2), Inches(1.8), Inches(1.8), Inches(2.1)]
    matrix_data = [
        ["Penguji 1", "Modul A: Autentikasi, OTP & Sesi", "Modul B: Face Recognition & Biometrik", "Brute-force, OTP expiry, Session Hijacking, Face Spoofing, dan Ambiguous Distance."],
        ["Penguji 2", "Modul C: Presensi & Rekap Kehadiran", "Modul D: Manajemen Perizinan & Sakit", "Race condition absensi ganda, Time Travel attack, Upload File Berbahaya, dan Self-Approval."],
        ["Penguji 3", "Modul E: Manajemen Jadwal Pelajaran", "Modul F: Data Master Siswa & Kelas", "Overlap jam/guru/ruang, Time formatting, SQL Injection search, dan Foreign Key cascade delete."],
        ["Penguji 4", "Modul G: Pengumuman & Notifikasi", "Modul H: RBAC, Pengaturan & Backup DB", "Stored XSS, Broadcast flooding, Privilege Escalation via profile, IDOR, dan Path Traversal backup."]
    ]
    
    tbl_matrix = doc.add_table(rows=len(matrix_data) + 1, cols=4)
    format_table(tbl_matrix, matrix_widths, matrix_headers, matrix_data)
    
    p_spc = doc.add_paragraph()
    p_spc.paragraph_format.space_after = Pt(10)
    
    # BAB 3
    h3 = doc.add_heading(level=1)
    run_h3 = h3.add_run("3. Rincian Lengkap Skenario Pengujian Negatif")
    run_h3.font.name = "Calibri"
    run_h3.font.size = Pt(13)
    run_h3.font.bold = True
    run_h3.font.color.rgb = RGBColor(30, 58, 138)
    
    headers_scenarios = ["ID Uji", "Skenario Uji Negatif", "Langkah / Input Anomali", "Hasil yang Diharapkan", "Tingkat Risiko", "Status"]
    col_widths_scenarios = [Inches(0.8), Inches(1.4), Inches(1.7), Inches(1.8), Inches(0.7), Inches(0.5)]

    # --- PENGUJI 1 ---
    h_p1 = doc.add_heading(level=2)
    run_hp1 = h_p1.add_run("3.1 Penguji 1: Identity & Biometric Security")
    run_hp1.font.name = "Calibri"
    run_hp1.font.size = Pt(11.5)
    run_hp1.font.bold = True
    run_hp1.font.color.rgb = RGBColor(37, 99, 235)
    
    # Modul A
    p_mod_a = doc.add_paragraph()
    run_ma = p_mod_a.add_run("A. Modul Autentikasi, OTP & Manajemen Sesi (AuthController, RegisterController, ForgotPasswordController)")
    run_ma.bold = True
    run_ma.font.size = Pt(10)
    p_mod_a.paragraph_format.space_after = Pt(4)
    
    data_mod_a = [
        ["NEG-A01", "Brute-Force OTP Lupa Password", "Kirim 100 request POST /password/verify-code dalam 15 detik dengan kode acak 6 digit.", "Rate limiting aktif (HTTP 429 atau 423 Locked) setelah 3-5x percobaan gagal. OTP hangus.", "Critical", "[  ]"],
        ["NEG-A02", "Bypass Reset Password Tanpa OTP", "Kirim POST /password/reset langsung tanpa melalui proses verifikasi kode OTP sebelumnya.", "Ditolak HTTP 403 Forbidden: Sesi verifikasi tidak sah atau expired.", "Critical", "[  ]"],
        ["NEG-A03", "Manipulasi Karakter Email Registrasi", "Daftar dengan email berspasi ' user@mail.com ', atau unicode homoglyph 'admín@school.id'.", "Sistem membersihkan spasi/format atau menolak. Tidak terjadi duplikasi akun tersembunyi.", "High", "[  ]"],
        ["NEG-A04", "Replay Sesi Pasca Logout", "Simpan cookie sesi login, lakukan logout, lalu akses endpoint terlindungi dengan cookie lama.", "Ditolak (HTTP 302/401 ke /login). Session ID lama wajib dihancurkan di storage server.", "High", "[  ]"],
        ["NEG-A05", "Bcrypt DoS via Password Sangat Panjang", "Kirim password berisi 15.000 karakter pada form registrasi / login untuk membebani CPU hash.", "Validasi menolak panjang string sebelum komputasi hash dijalankan (maks 255 karakter).", "Medium", "[  ]"]
    ]
    tbl_a = doc.add_table(rows=len(data_mod_a)+1, cols=6)
    format_table(tbl_a, col_widths_scenarios, headers_scenarios, data_mod_a)
    
    doc.add_paragraph().paragraph_format.space_after = Pt(6)
    
    # Modul B
    p_mod_b = doc.add_paragraph()
    run_mb = p_mod_b.add_run("B. Modul Face Recognition & Biometrik Presensi (FaceRecognitionController, /scan, /face/*)")
    run_mb.bold = True
    run_mb.font.size = Pt(10)
    p_mod_b.paragraph_format.space_after = Pt(4)
    
    data_mod_b = [
        ["NEG-B01", "Face Spoofing Foto Digital / Cetak", "Arahkan foto selfie siswa dari layar HP lain atau cetakan foto kertas ke arah kamera scanner.", "Liveness detection menolak presensi, atau skor kemiripan tidak mencukupi untuk verifikasi.", "Critical", "[  ]"],
        ["NEG-B02", "Daftar Wajah Siswa Lain (IDOR)", "Login sebagai Siswa A, panggil POST /face/register dengan payload NISN milik Siswa B.", "Ditolak HTTP 403: 'Anda hanya dapat mendaftarkan wajah untuk akun sendiri'.", "Critical", "[  ]"],
        ["NEG-B03", "Payload Descriptor Rusak / Corrupted", "Kirim array descriptor buatan: array kosong [], float tidak valid [NaN, null], atau dimensi keliru.", "Validasi menolak (HTTP 422). Fungsi euclidean distance tidak crash fatal HTTP 500.", "High", "[  ]"],
        ["NEG-B04", "Deteksi Wajah Ganda Sekaligus", "Dua atau tiga orang berdiri bersamaan dalam frame kamera saat melakukan scan presensi.", "Sistem menampilkan alert 'Lebih dari satu wajah terdeteksi' dan membatalkan submit.", "Medium", "[  ]"],
        ["NEG-B05", "Ambiguitas Wajah Kembar / Mirip", "Uji dua siswa berwajah sangat mirip yang menghasilkan jarak kemiripan di bawah threshold.", "Sistem mendeteksi selisih kandidat pertama & kedua terlalu tipis dan meminta konfirmasi identitas.", "High", "[  ]"]
    ]
    tbl_b = doc.add_table(rows=len(data_mod_b)+1, cols=6)
    format_table(tbl_b, col_widths_scenarios, headers_scenarios, data_mod_b)

    doc.add_paragraph().paragraph_format.space_after = Pt(10)

    # --- PENGUJI 2 ---
    h_p2 = doc.add_heading(level=2)
    run_hp2 = h_p2.add_run("3.2 Penguji 2: Presensi, Kehadiran & Perizinan")
    run_hp2.font.name = "Calibri"
    run_hp2.font.size = Pt(11.5)
    run_hp2.font.bold = True
    run_hp2.font.color.rgb = RGBColor(37, 99, 235)

    # Modul C
    p_mod_c = doc.add_paragraph()
    run_mc = p_mod_c.add_run("C. Modul Pencatatan Absensi & Rekapitulasi (AttendanceController, /attendance/*)")
    run_mc.bold = True
    run_mc.font.size = Pt(10)
    p_mod_c.paragraph_format.space_after = Pt(4)

    data_mod_c = [
        ["NEG-C01", "Race Condition Presensi Simultan", "Kirim 5 request POST /attendance/store secara serentak dalam selisih 50ms untuk NISN yang sama.", "Hanya 1 baris kehadiran yang tersimpan; 4 request lainnya ditolak dengan notifikasi 'Sudah absen'.", "Critical", "[  ]"],
        ["NEG-C02", "Presensi di Luar Jam Pelajaran Aktif", "Kirim presensi pada hari libur atau pukul 23:00 malam untuk jadwal pelajaran pagi.", "Ditolak dengan pesan: 'Tidak ada jadwal aktif saat ini'. Data tidak masuk rekap valid.", "High", "[  ]"],
        ["NEG-C03", "Manipulasi Jam Device Client", "Ubah waktu perangkat client menjadi tanggal kemarin atau masa depan sebelum menekan tombol absen.", "Waktu presensi tetap mengacu pada Carbon::now() server. Manipulasi jam client diabaikan.", "High", "[  ]"],
        ["NEG-C04", "Export Rekap Rentang Tanggal Terbalik", "Pilih filter tanggal mulai 31-12-2026 dan tanggal selesai 01-01-2026 pada form export.", "Validasi form menolak: 'Tanggal akhir harus setelah tanggal mulai'. File tidak korup.", "Medium", "[  ]"],
        ["NEG-C05", "Formula Injection pada Catatan Absensi", "Isi catatan absensi manual dengan formula Excel: =cmd|'/C calc'!A0 atau =HYPERLINK(...).", "Karakter formula di-escape dengan tanda petik (') saat diexport ke CSV/Excel.", "High", "[  ]"]
    ]
    tbl_c = doc.add_table(rows=len(data_mod_c)+1, cols=6)
    format_table(tbl_c, col_widths_scenarios, headers_scenarios, data_mod_c)

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # Modul D
    p_mod_d = doc.add_paragraph()
    run_md = p_mod_d.add_run("D. Modul Manajemen Perizinan, Sakit & Dispensasi (PermissionController, /permissions/*)")
    run_md.bold = True
    run_md.font.size = Pt(10)
    p_mod_d.paragraph_format.space_after = Pt(4)

    data_mod_d = [
        ["NEG-D01", "Upload File Berbahaya Surat Dokter", "Upload file shell backdoor surat_sakit.php.jpg atau script biner executable sebagai bukti.", "Validasi MIME tipe dan ekstensi menolak keras file selain JPG/PNG/PDF. File tidak tersimpan.", "Critical", "[  ]"],
        ["NEG-D02", "Siswa Approval Izin Sendiri", "Login akun siswa, kirim request POST /permissions/{id}/status dengan payload status=approved.", "Ditolak HTTP 403 Forbidden. Hanya role guru/admin yang berwenang mengubah status persetujuan.", "Critical", "[  ]"],
        ["NEG-D03", "Pengajuan Izin Bertanggal Tumpang Tindih", "Ajukan izin dispen tanggal 10-15 Mei saat permohonan sakit tanggal 12-14 Mei sudah ada.", "Sistem mendeteksi jadwal overlap dan menolak duplikasi izin pada tanggal yang sama.", "High", "[  ]"],
        ["NEG-D04", "Pengajuan Izin Mundur Ekstrem", "Ajukan izin untuk tanggal 6 bulan yang lalu guna membatalkan catatan alpha lampau.", "Sistem menolak permohonan izin tanggal lampau di luar toleransi operasional sekolah.", "Medium", "[  ]"],
        ["NEG-D05", "Akses Izin Siswa Lain (IDOR)", "Orang tua Siswa A membuka URL /permissions/{id_izin_siswa_B} secara langsung.", "Sistem memvalidasi kepemilikan relasi keluarga dan menolak jika bukan anaknya (HTTP 403).", "High", "[  ]"]
    ]
    tbl_d = doc.add_table(rows=len(data_mod_d)+1, cols=6)
    format_table(tbl_d, col_widths_scenarios, headers_scenarios, data_mod_d)

    doc.add_paragraph().paragraph_format.space_after = Pt(10)

    # --- PENGUJI 3 ---
    h_p3 = doc.add_heading(level=2)
    run_hp3 = h_p3.add_run("3.3 Penguji 3: Akademik & Master Data Integritas")
    run_hp3.font.name = "Calibri"
    run_hp3.font.size = Pt(11.5)
    run_hp3.font.bold = True
    run_hp3.font.color.rgb = RGBColor(37, 99, 235)

    # Modul E
    p_mod_e = doc.add_paragraph()
    run_me = p_mod_e.add_run("E. Modul Manajemen Jadwal Pelajaran (ScheduleController, /schedules/*)")
    run_me.bold = True
    run_me.font.size = Pt(10)
    p_mod_e.paragraph_format.space_after = Pt(4)

    data_mod_e = [
        ["NEG-E01", "Waktu Selesai Mendahului Waktu Mulai", "Input jadwal dengan start_time = 14:00 dan end_time = 08:00.", "Validasi form menolak: 'Waktu selesai harus setelah waktu mulai'. Data gagal tersimpan.", "High", "[  ]"],
        ["NEG-E02", "Bentrok Jadwal Guru Mengajar 2 Kelas", "Jadwalkan Guru X di Kelas A (08:00-09:30) dan di Kelas B (09:00-10:00) pada hari sama.", "Sistem mendeteksi bentrok guru dan menolak pembuatan jadwal kedua.", "Critical", "[  ]"],
        ["NEG-E03", "Bentrok Jadwal Kelas / Ruangan", "Buat 2 mata pelajaran berbeda untuk kelas yang sama pada jam dan hari yang bertabrakan.", "Sistem menolak penyimpanan dengan peringatan: 'Kelas sudah memiliki jadwal aktif di jam ini'.", "High", "[  ]"],
        ["NEG-E04", "Format Waktu Tidak Valid / Ekstrem", "Kirim payload API schedule dengan start_time = '25:70' atau '-05:00'.", "Validasi format waktu (date_format:H:i) menggagalkan request dengan HTTP 422.", "Medium", "[  ]"],
        ["NEG-E05", "Hapus Jadwal dengan Log Kehadiran Aktif", "Hapus entri jadwal saat jam pelajaran sedang berjalan dan sudah memiliki ratusan log absen.", "Foreign key integrity mencegah penghapusan langsung, atau dilakukan soft delete tanpa merusak rekap.", "Critical", "[  ]"]
    ]
    tbl_e = doc.add_table(rows=len(data_mod_e)+1, cols=6)
    format_table(tbl_e, col_widths_scenarios, headers_scenarios, data_mod_e)

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # Modul F
    p_mod_f = doc.add_paragraph()
    run_mf = p_mod_f.add_run("F. Modul Data Master Siswa & Kelas (StudentsController, ClassesController, /students/*, /classes/*)")
    run_mf.bold = True
    run_mf.font.size = Pt(10)
    p_mod_f.paragraph_format.space_after = Pt(4)

    data_mod_f = [
        ["NEG-F01", "Duplikasi NISN Siswa", "Daftarkan siswa baru atau edit profil siswa dengan NISN yang sudah terdaftar di database.", "Validasi unique:students,nisn menggagalkan penyimpanan dan memunculkan error duplikat.", "Critical", "[  ]"],
        ["NEG-F02", "Hapus Kelas dengan Siswa Aktif", "Hapus kelas '10-RPL-1' yang masih berelasi dengan 35 siswa aktif tanpa memindahkan siswa.", "Sistem menolak penghapusan (*foreign key restrict*) dan mewajibkan mutasi siswa terlebih dahulu.", "Critical", "[  ]"],
        ["NEG-F03", "SQL Injection pada Filter & Search", "Ketikkan payload SQL: ' OR '1'='1' -- atau '; DROP TABLE students; pada input filter siswa.", "Query builder Laravel menjalankan parameterized query; tidak terjadi kebocoran atau kerusakan DB.", "Critical", "[  ]"],
        ["NEG-F04", "Injeksi Karakter Ekstrem pada Nama", "Input nama siswa: 1000 karakter huruf, simbol biner, atau tag script <script>alert(1)</script>.", "Karakter dibatasi maksimum 255 huruf dan tag HTML di-escape pada tampilan Blade.", "High", "[  ]"],
        ["NEG-F05", "Penetapan ID Kelas Fiktif (Orphan)", "Kirim request penambahan siswa dengan class_id = 999999 (ID kelas tidak terdaftar).", "Validasi exists:classes,id menolak request dengan status HTTP 422.", "Medium", "[  ]"]
    ]
    tbl_f = doc.add_table(rows=len(data_mod_f)+1, cols=6)
    format_table(tbl_f, col_widths_scenarios, headers_scenarios, data_mod_f)

    doc.add_paragraph().paragraph_format.space_after = Pt(10)

    # --- PENGUJI 4 ---
    h_p4 = doc.add_heading(level=2)
    run_hp4 = h_p4.add_run("3.4 Penguji 4: Komunikasi, Administrasi & Keamanan Sistem")
    run_hp4.font.name = "Calibri"
    run_hp4.font.size = Pt(11.5)
    run_hp4.font.bold = True
    run_hp4.font.color.rgb = RGBColor(37, 99, 235)

    # Modul G
    p_mod_g = doc.add_paragraph()
    run_mg = p_mod_g.add_run("G. Modul Pengumuman & Sistem Notifikasi (AnnouncementsController, NotificationsController)")
    run_mg.bold = True
    run_mg.font.size = Pt(10)
    p_mod_g.paragraph_format.space_after = Pt(4)

    data_mod_g = [
        ["NEG-G01", "Stored XSS pada Judul & Isi Pengumuman", "Buat pengumuman berisi payload: <img src=x onerror=alert('XSS-Eduface')> atau script iframe.", "Blade escaping ({{ }}) menetralkan eksekusi script saat pengumuman dibuka oleh murid/guru.", "Critical", "[  ]"],
        ["NEG-G02", "Manipulasi Target Audiens Pengumuman", "Kirim request penambahan pengumuman dengan target_role = 'superhacker' atau array kosong [].", "Validasi in:all,teacher,student,parent menolak payload dengan pesan error validasi.", "Medium", "[  ]"],
        ["NEG-G03", "Hapus Notifikasi User Lain (IDOR)", "Kirim DELETE /notifications/destroy-all dengan menyisipkan parameter user_id milik akun lain.", "Sistem hanya menghapus baris notifikasi milik user session aktif (Auth::id()). Notifikasi lain aman.", "High", "[  ]"],
        ["NEG-G04", "Spam Flooding Pengumuman Broadcast", "Kirim request pembuatan pengumuman massal secara looping cepat (50x dalam 1 menit).", "Rate limiting aktif untuk posting konten broadcast agar tabel notifikasi tidak membengkak (DoS DB).", "High", "[  ]"],
        ["NEG-G05", "Bypass Hak Akses Pengumuman Rahasia", "Siswa membuka URL pengumuman internal guru /announcements/{id_khusus_guru}.", "Sistem mengecek kecocokan target role dan menolak akses jika role siswa tidak diizinkan (HTTP 403).", "High", "[  ]"]
    ]
    tbl_g = doc.add_table(rows=len(data_mod_g)+1, cols=6)
    format_table(tbl_g, col_widths_scenarios, headers_scenarios, data_mod_g)

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    # Modul H
    p_mod_h = doc.add_paragraph()
    run_mh = p_mod_h.add_run("H. Modul RBAC, Pengaturan Sistem & Backup DB (SettingsController, UsersController, /settings/*)")
    run_mh.bold = True
    run_mh.font.size = Pt(10)
    p_mod_h.paragraph_format.space_after = Pt(4)

    data_mod_h = [
        ["NEG-H01", "Privilege Escalation via Update Profil", "Siswa mengirim PATCH /profile dengan menyisipkan parameter role='admin' atau is_admin=1.", "Parameter role diabaikan (dilindungi $fillable/controller). Role akun tetap sebagai siswa.", "Critical", "[  ]"],
        ["NEG-H02", "Bypass URL Endpoint Administrasi", "Login sebagai siswa atau orang tua, kemudian langsung mengakses URL /settings atau /users.", "Middleware Role menolak akses dengan HTTP 403 Forbidden atau redirect ke dashboard.", "Critical", "[  ]"],
        ["NEG-H03", "Path Traversal pada Download Backup", "Mengakses download backup dengan parameter: ?file=../../../../.env atau /etc/passwd.", "Sistem mengunci path hanya di direktori backup storage dan menolak traversal dot-slash (..).", "Critical", "[  ]"],
        ["NEG-H04", "Trigger Backup DB Simultan (Disk Exhaustion)", "Menembak POST /settings/backup puluhan kali bersamaan untuk memenuhi kapasitas storage server.", "Sistem menerapkan antrean/lock mutex; proses backup baru ditolak jika backup sebelumnya belum usai.", "High", "[  ]"],
        ["NEG-H05", "Hapus Akun Super Admin Terakhir", "Super Admin melakukan tindakan DELETE /users/{id_admin} terhadap dirinya sendiri.", "Sistem menolak dengan validasi khusus: 'Tidak dapat menghapus satu-satunya administrator aktif'.", "High", "[  ]"]
    ]
    tbl_h = doc.add_table(rows=len(data_mod_h)+1, cols=6)
    format_table(tbl_h, col_widths_scenarios, headers_scenarios, data_mod_h)

    doc.add_paragraph().paragraph_format.space_after = Pt(10)

    # BAB 4
    h4 = doc.add_heading(level=1)
    run_h4 = h4.add_run("4. Standar Pelaporan Temuan Bug (Defect Reporting)")
    run_h4.font.name = "Calibri"
    run_h4.font.size = Pt(13)
    run_h4.font.bold = True
    run_h4.font.color.rgb = RGBColor(30, 58, 138)

    p_rep = doc.add_paragraph(
        "Jika penguji menemukan skenario negatif yang GAGAL (sistem rentan, data rusak tembus ke database, "
        "atau terjadi crash HTTP 500 dengan stack trace terekspos), penguji wajib mendokumentasikan temuan dengan format berikut:"
    )
    p_rep.paragraph_format.space_after = Pt(6)

    bug_headers = ["Field Laporan", "Deskripsi / Standar Pengisian", "Contoh Isian Temuan"]
    bug_widths = [Inches(1.8), Inches(2.6), Inches(2.5)]
    bug_data = [
        ["ID Kasus Uji & Judul", "Sebutkan ID kasus uji dan ringkasan singkat kegagalan.", "NEG-D01: Upload file shell PHP lolos pada surat izin sakit."],
        ["Tingkat Keparahan (Severity)", "Pilih klasifikasi: Critical, High, Medium, atau Low.", "Critical (Celah eksekusi kode berbahaya di server)."],
        ["Langkah Reproduksi", "Urutan detail langkah untuk memicu bug kembali.", "1. Login akun ortu\n2. Buka /permissions/create\n3. Upload shell.php.jpg\n4. Submit."],
        ["Hasil Aktual (Actual Behavior)", "Apa yang terjadi saat pengujian dilakukan?", "File .php berhasil tersimpan di public/storage dan dapat diakses."],
        ["Hasil Harapan (Expected Behavior)", "Perilaku yang seharusnya ditunjukkan oleh sistem.", "Sistem menampilkan alert error: 'Format file tidak diizinkan' (HTTP 422)."],
        ["Tangkapan Layar / Bukti Log", "Lampirkan screenshot UI, response network tab, atau laravel.log.", "storage/logs/laravel.log: line 124, status code 200."]
    ]
    tbl_bug = doc.add_table(rows=len(bug_data)+1, cols=3)
    format_table(tbl_bug, bug_widths, bug_headers, bug_data)

    doc.add_paragraph().paragraph_format.space_after = Pt(10)

    # BAB 5
    h5 = doc.add_heading(level=1)
    run_h5 = h5.add_run("5. Rekomendasi Mitigasi Teknis untuk Tim Pengembang")
    run_h5.font.name = "Calibri"
    run_h5.font.size = Pt(13)
    run_h5.font.bold = True
    run_h5.font.color.rgb = RGBColor(30, 58, 138)

    recs = [
        ("Proteksi Mass Assignment & IDOR: ", "Pastikan semua model Eloquent menerapkan proteksi $guarded atau $fillable yang ketat. Pastikan setiap operasi Update/Delete memvalidasi user_id terhadap Auth::id() via Laravel Policy / Form Request."),
        ("Penguatan Algoritma Biometrik: ", "Terapkan ambiguitas check (selisih skor kandidat pertama dan kedua minimal 0.15) dan implementasikan liveness challenge sederhana (seperti berkedip atau senyum) sebelum descriptor diproses."),
        ("Database Transaction & Pessimistic Locking: ", "Gunakan DB::transaction() dan ->lockForUpdate() pada saat pencatatan absensi untuk memitigasi race condition saat ratusan siswa melakukan scan presensi secara bersamaan."),
        ("Validasi File Upload Berlapis: ", "Jangan hanya mengandalkan ekstensi file. Gunakan validasi MIME server-side (mimetypes:image/jpeg,image/png,application/pdf) dan simpan file di luar root web publik jika memungkinkan."),
        ("Global Rate Limiting: ", "Terapkan middleware throttle pada endpoint sensitif (POST /login, POST /password/verify-code, POST /register/send-otp, POST /settings/backup).")
    ]

    for title_rec, desc_rec in recs:
        p_r = doc.add_paragraph(style='List Bullet')
        p_r.paragraph_format.space_before = Pt(2)
        p_r.paragraph_format.space_after = Pt(2)
        r_b = p_r.add_run(title_rec)
        r_b.bold = True
        r_b.font.color.rgb = RGBColor(30, 58, 138)
        r_t = p_r.add_run(desc_rec)
        r_t.font.color.rgb = RGBColor(51, 65, 85)

    doc_path = os.path.abspath("Laporan_Skenario_Negative_Testing_Eduface.docx")
    doc.save(doc_path)
    print(f"Document successfully created at: {doc_path}")

if __name__ == "__main__":
    build_document()
