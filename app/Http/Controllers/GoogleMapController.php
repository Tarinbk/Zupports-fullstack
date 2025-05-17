<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleMapController extends Controller
{
    // ฟังก์ชัน search สำหรับค้นหาร้านอาหารจาก Google Maps API
    public function search(Request $request)
    {
        // รับค่าคีย์เวิร์ดจาก request parameter ถ้าไม่มีให้ใช้ค่าเริ่มต้นเป็น 'บางซื่อ'
        $keyword = $request->input('keyword', 'บางซื่อ');
        // สร้าง key สำหรับ cache โดยใช้ md5 ของ keyword เพื่อให้ key มีความเฉพาะตัว
        $cacheKey = 'google_places_' . md5($keyword);

        // ถ้ามีข้อมูลใน cache อยู่แล้ว ให้ return ค่า JSON จาก cache ทันที (ลดจำนวนครั้งที่เรียก API)
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // ดึง API key จากไฟล์ .env (ควรเก็บ API key ไว้ใน .env เพื่อความปลอดภัย)
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        // ส่ง GET request ไปยัง Google Places API โดยระบุ query (ค้นหาร้านอาหาร + keyword), key และภาษาที่ใช้
        $response = Http::get("https://maps.googleapis.com/maps/api/place/textsearch/json", [
            'query' => "ร้านอาหาร " . $keyword,
            'key' => $apiKey,
            'language' => 'th'
        ]);

        $data = $response->json();
        // เก็บข้อมูลที่ได้ลงใน cache เป็นเวลา 10 นาที เพื่อป้องกันการเรียก API ซ้ำบ่อย
        Cache::put($cacheKey, $data, now()->addMinutes(10));

        return response()->json($data);
    }
}
