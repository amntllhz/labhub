import customtkinter as ctk
from tkinter import messagebox
import requests
import webbrowser
import threading
import os
from PIL import Image

# Set tema (Opsional agar lebih modern)
ctk.set_appearance_mode("dark") 
ctk.set_default_color_theme("blue")

class App(ctk.CTk):
    def __init__(self):
        super().__init__()        

        # 1. Tentukan lokasi folder script ini berada (folder 'desktop')
        basedir = os.path.dirname(__file__)
        
        # 2. Gabungkan dengan lokasi file icon
        icon_path = os.path.join(basedir, "assets", "labhub-favicon.ico")
        image_path = os.path.join(basedir, "assets", "labhub-logo.png")

        # Konfigurasi Window
        self.title("LabHub - Fastikom")
        self.geometry("500x450")

        # Ganti Icon di sini (Gunakan file .ico)
        self.iconbitmap(icon_path)

        # Tentukan 'size' agar gambar tidak pecah atau terlalu besar (sesuaikan px-nya)
        self.logo_image = ctk.CTkImage(
            light_image=Image.open(image_path),
            dark_image=Image.open(image_path),
            size=(100, 20) # Sesuaikan (lebar, tinggi) gambar kamu
        )

        # 3. Tampilkan gambar di dalam Label (Ganti judul lama)
        self.title_image_label = ctk.CTkLabel(self, image=self.logo_image, text="") 
        self.title_image_label.pack(padx=10, pady=(40, 30))

        # Data Pilihan
        pilihan_kelas = ["Pagi", "Malam"]
        pilihan_lab = ["LABKOM-1", "LABKOM-2"]

        # Container untuk Form (Agar lebih rapi daripada .place)
        self.form_frame = ctk.CTkFrame(self, fg_color="transparent")
        self.form_frame.pack(padx=100, fill="both", expand=True)

        # UI Elements (Menggunakan Grid di dalam frame agar presisi)
        self.create_label_entry("Nama :", 0)
        self.entry_nama = ctk.CTkEntry(self.form_frame, width=220)
        self.entry_nama.grid(row=0, column=1, pady=10)

        self.create_label_entry("NIM :", 1)
        self.entry_nim = ctk.CTkEntry(self.form_frame, width=220)
        self.entry_nim.grid(row=1, column=1, pady=10)

        self.create_label_entry("Kelas :", 2)
        self.entry_kelas = ctk.CTkComboBox(self.form_frame, width=220, values=pilihan_kelas)
        self.entry_kelas.grid(row=2, column=1, pady=10)

        self.create_label_entry("LAB :", 3)
        self.entry_lab = ctk.CTkComboBox(self.form_frame, width=220, values=pilihan_lab)
        self.entry_lab.grid(row=3, column=1, pady=10)

        self.create_label_entry("Keluhan :", 4)
        self.entry_keluhan = ctk.CTkEntry(self.form_frame, width=220)
        self.entry_keluhan.grid(row=4, column=1, pady=10)

        # Button Container
        self.btn_frame = ctk.CTkFrame(self, fg_color="transparent")
        self.btn_frame.pack(pady=30)

        self.btn_kirim = ctk.CTkButton(self.btn_frame, width=120, text="KIRIM", 
                                       fg_color="#0087f2", border_width=0,
                                       command=self.handle_insert)
        self.btn_kirim.grid(row=0, column=0, padx=10)

        self.btn_lihat = ctk.CTkButton(self.btn_frame, width=120, text="LIHAT DATA", 
                                       fg_color="transparent", border_width=1,
                                       command=self.lihat_data)
        self.btn_lihat.grid(row=0, column=1, padx=10)

        # Tambahkan Progress Bar (Animasi Loading) di bawah tombol
        self.loading_bar = ctk.CTkProgressBar(self, orientation="horizontal", width=300)
        self.loading_bar.set(0) # Mulai dari 0
        self.loading_bar.configure(mode="indeterminate") # Animasi bolak-balik

    def create_label_entry(self, text, row):
        label = ctk.CTkLabel(self.form_frame, text=text, anchor="w")
        label.grid(row=row, column=0, sticky="w", padx=(0, 20))

    def handle_insert(self):
        # Ambil data
        nama = self.entry_nama.get().strip()
        nim = self.entry_nim.get().strip()
        keluhan = self.entry_keluhan.get().strip()
        
        # --- 1. Validasi Input (Tetap di Main Thread) ---
        if not nama or not nim or not keluhan:
            messagebox.showerror("Error", "Semua field wajib diisi !")
            return

        if not nama.replace(" ", "").isalpha():
            messagebox.showerror("Validation Error", "Nama hanya boleh berisi huruf !")
            return

        if not nim.isdigit():
            messagebox.showerror("Validation Error", "NIM hanya boleh berisi angka !")
            return

        # Siapkan data
        data = {
            "nim": nim,
            "nama": nama,
            "kelas": self.entry_kelas.get(),
            "lab": self.entry_lab.get(),
            "keluhan": keluhan
        }

        # --- 2. Siapkan UI untuk Loading ---
        self.loading_bar.pack(pady=10)
        self.loading_bar.start() 
        self.btn_kirim.configure(state="disabled", text="Mengirim...")

        # --- 3. Jalankan Thread (Hanya Satu Kali) ---
        # Ini akan memanggil send_data_task di background
        thread = threading.Thread(target=self.send_data_task, args=(data,))
        thread.daemon = True # Agar thread mati jika aplikasi ditutup
        thread.start()
    
    def send_data_task(self, data):
        """Fungsi ini berjalan di background"""
        try:
            url = "http://localhost/TUGASAKHIR/web/api/create_report.php"
            response = requests.post(url, data=data, timeout=10)
            res_data = response.json()
            
            # Kembali ke main thread untuk update UI
            self.after(0, lambda: self.finish_insert(res_data))
        except Exception as e:
            self.after(0, lambda: self.finish_insert({"status": "error", "message": str(e)}))
    
    def finish_insert(self, res_data):
        """Kembali mereset UI setelah selesai"""
        self.loading_bar.stop()
        self.loading_bar.pack_forget() # Sembunyikan loading
        self.btn_kirim.configure(state="normal", text="KIRIM")

        if res_data['status'] == 'success':
            messagebox.showinfo("Berhasil", "Laporan anda berhasil terkirim!")
            self.clear_fields()
        else:
            messagebox.showerror("Gagal", res_data['message'])

    def clear_fields(self):
        self.entry_nama.delete(0, 'end')
        self.entry_nim.delete(0, 'end')
        self.entry_keluhan.delete(0, 'end')

    def lihat_data(self):
        # Arahkan ke folder web/index.php yang baru
        url = "http://localhost/TUGASAKHIR/web/index.php"
        webbrowser.open(url)

if __name__ == "__main__":
    app = App()
    app.mainloop()