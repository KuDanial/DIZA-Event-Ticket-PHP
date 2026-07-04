import os

style_path = r"C:\xampp\htdocs\DIZA-Event-Ticket-PHP\css\style.css"

with open(style_path, "r", encoding="utf-8") as f:
    lines = f.readlines()

for i, line in enumerate(lines):
    if "hero" in line or "background" in line:
        print(f"Line {i+1}: {line.strip()}")
