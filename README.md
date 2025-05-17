📘 คำอธิบายโปรเจกต์: Food-map
ระบบเว็บแอปแสดงรายชื่อร้านอาหารโดยใช้ Google Maps API (Places API + Maps JavaScript API)
Frontend ใช้ Vue 3 + Vite
Backend ใช้ Laravel 10 เป็น API Proxy และ Cache
UI สร้างด้วย Bootstrap 5 และ Responsive ครบถ้วน

⚙️ ขั้นตอนการติดตั้งและใช้งาน (Local)
1. Clone โปรเจกต์
 - git clone https://github.com/Tarinbk/Zupports-fullstack.git
 - cd Zupports-fullstack

3. ติดตั้ง Laravel
 - composer create-project "laravel/laravel:^10.0" food-map
 - cd food-map

5. ติดตั้ง Vue + Vite + Bootstrap
 - npm install
 - npm install vue@3 bootstrap@5
 - npm install @vitejs/plugin-vue

7. ตั้งค่า API Key ของ Google
 - เปิดไฟล์ .env แล้วเพิ่มบรรทัดนี้:
 - GOOGLE_MAPS_API_KEY=AIzaSyDEMIDu7mGSvSy_kHAbVyX0TZgXN-_hSdY

9. รันเซิร์ฟเวอร์
 - npm run dev        # รัน frontend
 - php artisan serve  # รัน backend
 - เปิดที่: http://localhost:8000


🔌 API ที่ใช้งาน

GET /api/restaurants?keyword=บางซื่อ

 - รับ keyword ร้านอาหาร
 - ดึงข้อมูลจาก Google Places API
 - แคชผลลัพธ์ 10 นาที (ลดการเรียกซ้ำ)
 - ส่งกลับ JSON รายชื่อร้าน

