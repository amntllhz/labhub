from tkinter import *
import customtkinter as ctk
from tkinter import messagebox
import mysql.connector
import webbrowser

# variable frame

root = ctk.CTk()
root.geometry("500x500")
root.title("Report Page")

# judul frame

title_label = ctk.CTkLabel(root,text="PELAPORAN KENDALA JARINGAN LABKOM", font= ctk.CTkFont(size=20, weight="bold"))
title_label.pack(padx=10, pady=(50,0))

# variable combo box

pilihan1 = ["MALAM","PAGI"]
pilihan2 = ["LAB1","LAB2"]

# connection

connection = mysql.connector.connect(host="localhost", user="root", password="", port="3306", database="main")
c = connection.cursor()



# Label


label_nama = ctk.CTkLabel(root, text="Nama :")
label_nim = ctk.CTkLabel(root, text="NIM :")
label_kelas = ctk.CTkLabel(root, text="Kelas :")
label_lab = ctk.CTkLabel(root, text="LAB :")
label_keluhan = ctk.CTkLabel(root, text="Keluhan :")


# Entry


entry_nama = ctk.CTkEntry(root, width=200)
entry_nim = ctk.CTkEntry(root, width=200)
entry_kelas = ctk.CTkComboBox(root, width=200, values=pilihan1)
entry_lab = ctk.CTkComboBox(root, width=200, values=pilihan2)
entry_keluhan = ctk.CTkEntry(root, width=200)


# Layout


label_nama.place(x=100, y=125)
label_nim.place(x=100, y=175)
label_kelas.place(x=100, y=225)
label_lab.place(x=100, y=275)
label_keluhan.place(x=100, y=325)


entry_nama.place(x=200, y=125)
entry_nim.place(x=200, y=175)
entry_kelas.place(x=200, y=225)
entry_lab.place(x=200, y=275)
entry_keluhan.place(x=200, y=325)


# Function


def error():
    messagebox.showerror(title="Halo", message="Anda Harus Mengisi Semua Field!")

def selesai():
    messagebox.showinfo(title="Halo", message="Terima kasih atas laporannya, akan segera kami tangani")

def clear():
    entry_nama.delete(0, END)
    entry_nim.delete(0, END)
    entry_keluhan.delete(0, END)

def sesuai():
    nim = entry_nim.get()
    nama = entry_nama.get()
    kelas = entry_kelas.get()
    lab = entry_lab.get()
    keluhan = entry_keluhan.get()



    insert_query = "INSERT INTO `datakendala`(`nim`, `nama`, `kelas`, `lab`, `keluhan`) VALUES(%s,%s,%s,%s,%s)"
    vals = (nim, nama, kelas, lab, keluhan)
    c.execute(insert_query, vals)
    connection.commit()


def insertData():
    nim = entry_nim.get()
    nama = entry_nama.get()
    kelas = entry_kelas.get()
    lab = entry_lab.get()
    keluhan = entry_keluhan.get()


    if nim == "":
        error()
    elif nama == "":
        error()
    elif keluhan == "":
        error()
    else:
        sesuai()
        selesai()
        clear()


def lihat_data():
    url = "http://localhost/tugasakhir/Pagination.php"
    webbrowser.open(url)


# Button

button = ctk.CTkButton(root, width=100, text="KIRIM", command=insertData)
button.place(x=150, y=375)


button = ctk.CTkButton(root, width=100, text="LIHAT DATA", command=lihat_data)
button.place(x=260, y=375)

# memunculkan frame

root.mainloop()