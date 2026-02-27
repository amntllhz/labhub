import customtkinter as ctk
from tkinter import messagebox
import requests
import webbrowser

# Set tema (Opsional agar lebih modern)
ctk.set_appearance_mode("dark") 
ctk.set_default_color_theme("blue")

class App(ctk.CTk):
    def __init__(self):
        super().__init__()

        # Konfigurasi Window
        self.title("LABHUB - FASTIKOM")
        self.geometry("500x450")

        # Judul
        self.title_label = ctk.CTkLabel(self, text="PELAPORAN KENDALA LABKOM", 
                                        font=ctk.CTkFont(size=18, weight="bold"))
        self.title_label.pack(padx=10, pady=(40, 30))

        # Data Pilihan
        pilihan_kelas = ["MALAM", "PAGI"]
        pilihan_lab = ["LAB1", "LAB2"]

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
                                       command=self.handle_insert)
        self.btn_kirim.grid(row=0, column=0, padx=10)

        self.btn_lihat = ctk.CTkButton(self.btn_frame, width=120, text="LIHAT DATA", 
                                       fg_color="transparent", border_width=2,
                                       command=self.lihat_data)
        self.btn_lihat.grid(row=0, column=1, padx=10)

    def create_label_entry(self, text, row):
        label = ctk.CTkLabel(self.form_frame, text=text, anchor="w")
        label.grid(row=row, column=0, sticky="w", padx=(0, 20))

    def handle_insert(self):
        # Ambil data
        nama = self.entry_nama.get().strip()
        nim = self.entry_nim.get().strip()
        keluhan = self.entry_keluhan.get().strip()
        
        # 1. Validasi Field Kosong
        if not nama or not nim or not keluhan:
            messagebox.showerror("Error", "Semua field (Nama, NIM, Keluhan) wajib diisi!")
            return

        # 2. Validasi Nama (Hanya huruf dan spasi)
        # Kita hilangkan spasi sementara untuk pengecekan .isalpha()
        if not nama.replace(" ", "").isalpha():
            messagebox.showerror("Validation Error", "Nama hanya boleh berisi huruf!")
            return

        # 3. Validasi NIM (Hanya angka)
        if not nim.isdigit():
            messagebox.showerror("Validation Error", "NIM hanya boleh berisi angka!")
            return

        # Jika lolos validasi, baru kirim ke API
        data = {
            "nim": nim,
            "nama": nama,
            "kelas": self.entry_kelas.get(),
            "lab": self.entry_lab.get(),
            "keluhan": keluhan
        }

        # Kirim ke API PHP
        try:
            url = "http://localhost/TUGASAKHIR/web/api/create_report.php"
            response = requests.post(url, data=data)
            
            if response.status_code == 200:
                res_data = response.json()
                if res_data['status'] == 'success':
                    messagebox.showinfo("Berhasil", "Terima kasih atas laporannya!")
                    self.clear_fields()
                else:
                    messagebox.showerror("Gagal", res_data['message'])
            else:
                messagebox.showerror("Error", "Gagal terhubung ke server.")
        except Exception as e:
            messagebox.showerror("Error", f"Terjadi kesalahan: {e}")

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