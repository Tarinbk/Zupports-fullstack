<template>
    <div class="container py-4">
        <!-- หัวข้อหลักของหน้าเว็บ แสดงข้อความ "ค้นหาร้านอาหาร" -->
        <h1 class="text-center mb-4">🔍 ค้นหาร้านอาหาร</h1>
        <!-- ช่อง input และปุ่มค้นหา ที่อยู่ในกล่อง input-group -->
        <div class="input-group mb-4 shadow-sm search-input-group">
            <!-- ช่องกรอกคำค้นหา ผูกข้อมูลกับตัวแปร keyword, กด Enter เรียกฟังก์ชัน searchRestaurants -->
            <input v-model="keyword" @keyup.enter="searchRestaurants" type="text"
                class="form-control form-control-lg custom-input" placeholder="ค้นหาร้านอาหาร เช่น บางซื่อ" />
            <!-- ปุ่มค้นหา กดแล้วเรียกฟังก์ชัน searchRestaurants -->
            <button class="btn btn-primary btn-lg" @click="searchRestaurants">
                ค้นหา
            </button>
        </div>
        <!-- แสดง spinner loading เมื่อกำลังค้นหา -->
        <div v-if="loading" class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">กำลังค้นหาร้านอาหาร...</p>
        </div>
        <!-- แสดงข้อความ error ถ้ามี error เกิดขึ้น -->
        <div v-if="error" class="alert alert-danger text-center">
            {{ error }}
        </div>
        <!-- แสดงผลลัพธ์ร้านอาหารเมื่อมีข้อมูล โดยใช้ grid ของ Bootstrap แสดงหลายคอลัมน์ -->
        <div v-if="restaurants.length > 0" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <!-- วนลูปร้านอาหารแต่ละตัว ด้วย v-for -->
            <div v-for="restaurant in restaurants" :key="restaurant.place_id" class="col">
                <div class="card h-100 shadow-sm border-0">
                    <!-- แสดงรูปภาพร้าน ถ้ามี photos -->
                    <img v-if="restaurant.photos && restaurant.photos.length"
                        :src="getPhotoUrl(restaurant.photos[0].photo_reference)" class="card-img-top"
                        alt="Restaurant" />
                    <!-- ชื่อร้านอาหาร -->
                    <div class="card-body">
                        <h5 class="card-title">{{ restaurant.name }}</h5>
                        <!-- ที่อยู่ร้านอาหาร -->
                        <p class="card-text">{{ restaurant.formatted_address }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end">
                        <!-- ปุ่มลิงก์เปิด Google Maps ในแท็บใหม่ -->
                        <a :href="getGoogleMapsLink(restaurant)" class="btn btn-outline-primary btn-sm google-maps-btn"
                            target="_blank">
                            ดูใน Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ถ้าไม่มีร้านอาหารเลย และไม่มีการโหลดและไม่มี error ให้แสดงข้อความแจ้งไม่พบร้าน -->
        <div v-if="restaurants.length === 0 && !loading && !error" class="text-center mt-5">
            <p class="text-muted">ไม่พบร้านอาหารในพื้นที่ที่ค้นหา</p>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            keyword: 'บางซื่อ',  // ตัวแปรเก็บคำค้นหาเริ่มต้นเป็น "บางซื่อ"
            restaurants: [], // ตัวแปรเก็บรายการร้านอาหาร (array)
            loading: false, // ตัวแปรสถานะ loading ว่ากำลังโหลดข้อมูลหรือไม่
            error: null, // ตัวแปรเก็บข้อความ error
        };
    },
    methods: {
        // ฟังก์ชันค้นหาร้านอาหารโดยเรียก API จาก backend
        searchRestaurants() {
            this.loading = true; // ตั้งสถานะ loading เป็น true เพื่อแสดง spinner
            this.error = null; // รีเซ็ต error เป็น null ทุกครั้งที่ค้นหาใหม่
            this.restaurants = []; // ล้างผลลัพธ์เดิมก่อน
            // เรียก fetch ไปยัง API /api/search โดยส่ง keyword ไปใน query string
            fetch(`/api/search?keyword=${encodeURIComponent(this.keyword)}`)
                .then((res) => res.json())
                .then((data) => {
                    if (data.error) {
                        this.error = data.message; // ถ้า API ส่ง error มา ให้ตั้งค่า error message
                    } else {
                        this.restaurants = data.results || []; // ถ้าไม่มี error ให้เก็บข้อมูลร้านอาหารที่ได้ใน restaurants
                    }
                    this.loading = false; // โหลดเสร็จแล้ว ปิด spinner
                })
                .catch(() => {
                    this.error = 'ไม่สามารถเชื่อมต่อ API ได้'; // กรณีเชื่อมต่อ API ไม่ได้ ให้แสดง error message
                    this.loading = false;
                });
        },
        // ฟังก์ชันสร้าง URL รูปภาพร้านอาหาร โดยใช้ photo_reference และ Google Maps API Key จาก environment
        getPhotoUrl(photoRef) {
            const key = import.meta.env.GOOGLE_MAPS_API_KEY;
            return `https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=${photoRef}&key=${key}`;
        },
        // ฟังก์ชันสร้างลิงก์เปิด Google Maps โดยใช้พิกัด lat/lng ของร้าน
        getGoogleMapsLink(place) {
            return `https://www.google.com/maps/search/?api=1&query=${place.geometry.location.lat},${place.geometry.location.lng}`;
        },
    },
    mounted() {
        this.searchRestaurants(); // เรียกฟังก์ชันค้นหาร้านอาหารเมื่อคอมโพเนนต์โหลดเสร็จ เพื่อแสดงผลเริ่มต้น
    },
};
</script>

<style scoped>
/* กำหนดสไตล์ให้ input ในกลุ่ม input-group มีความกว้างสูงสุด 600px และจัดกึ่งกลาง */
.input-group>input.form-control {
    max-width: 600px;
    margin: 0 auto;
    border-radius: 0.75rem;
    overflow: hidden;
    transition: box-shadow 0.3s ease;
    margin-right: 0.75rem;
}
/* ปุ่มค้นหาให้มีขนาดใหญ่และมีความโค้งมน */
.input-group>button.btn {
    padding: 1rem 1.75rem;
    font-size: 1.25rem;
    height: 3.5rem;
    border-radius: 0.75rem;
}
/* เปลี่ยนสีปุ่มเมื่อ hover */
.input-group>button.btn:hover {
    background-color: #073a86;
    color: white;
}

/* กำหนดความกว้างสูงสุดของกลุ่ม input และจัดกึ่งกลาง */
.search-input-group {
    max-width: 1150px;
    margin: 0 auto;
    margin-right: 840px;
}
/* กำหนดความกว้างสูงสุดของ search bar */
.search-bar {
    max-width: 800px;
    margin: 0 auto;
}
/* กำหนดขนาดฟอนต์ และ padding ให้ input */
.custom-input {
    font-size: 1.3rem;
    padding: 1rem 1.5rem;
    height: auto;
    border-radius: 0.5rem;
    flex: 1;
}
/* กำหนดสไตล์การแสดงผล card ให้มีเงา และ animation เวลา hover */
.card {
    display: flex;
    transition: transform 0.2s ease, box-shadow 0.3s ease;
    border-radius: 1rem;
    height: 100%;
}
/* เอฟเฟกต์เมื่อเอาเมาส์วางบนการ์ด */
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
}

/* รูปภาพใน card ให้มีขนาดกำหนด และจัดกึ่งกลาง */
.card-img-top {
    object-fit: cover;
    height: 170px;
    width: 180px;
    margin: auto;
    margin-top: 20px;
    margin-bottom: 10px;
    border-radius: 1rem;
    display: block;
    margin-left: 20px;
    margin-right: 20px;
}
/* เนื้อหาภายใน card body ให้มีความสูงขั้นต่ำ และจัดเนื้อหาให้อยู่กึ่งกลางแนวตั้ง */
.card-body {
    min-height: 180px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 1rem 1.25rem;
}
/* ชื่อร้านอาหารใน card title กำหนดขนาดฟอนต์และความหนา */
.card-title {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}
/* ข้อความที่อยู่ใน card text มีสีเทา และขยายพื้นที่ */
.card-text {
    flex-grow: 1;
    color: #555;
    font-size: 0.95rem;
    margin-bottom: 0;
}
/* กำหนดส่วนท้าย card footer เป็นโปร่งใส ไม่มีเส้นขอบ และจัดข้อความชิดขวา */
.card-footer {
    background-color: transparent;
    border: none;
    padding: 0.75rem 1.25rem;
    text-align: right;
}
/* ปุ่มลิงก์ Google Maps ให้โค้งมน และขนาดตัวอักษรเล็ก */
.btn-outline-primary {
    border-radius: 999px;
    font-size: 0.875rem;
    padding: 0.5rem 1.25rem;
}
/* ปุ่ม primary ให้ตัวอักษรหนา */
.btn-primary {
    font-weight: 600;
}
/* สไตล์ปุ่ม Google Maps ลิงก์แบบเส้นขอบสีน้ำเงิน ฟอนต์สีน้ำเงิน และพื้นหลังโปร่งใส */
.google-maps-btn {
    border: 1px solid #0d6efd;
    padding: 6px 12px;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    color: #0d6efd;
    background-color: transparent;
    transition: all 0.2s ease-in-out;
    display: inline-block;
    text-decoration: none;
}
/* เปลี่ยนสีปุ่ม Google Maps เมื่อ hover */
.google-maps-btn:hover {
    background-color: #0d6efd;
    color: white;
}


/* กำหนดสไตล์ responsive สำหรับหน้าจอเล็ก */
@media (max-width: 576px) {
    .card-img-top {
        height: 160px;
    }
}
</style>
