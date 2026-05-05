<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AIController extends Controller
{
    public function consult(Request $request)
    {
        $message = $request->input('message');
        $messageLow = mb_strtolower($message);

        // 1. Phân tích ý định người dùng (Intent Analysis - Simple)
        $intent = $this->parseIntent($messageLow);

        // 2. Tìm kiếm sản phẩm phù hợp
        $products = $this->findProducts($intent, $messageLow);

        // 3. Tạo phản hồi AI
        $response = $this->generateResponse($intent, $products);

        return response()->json([
            'response' => $response,
            'products' => $products->map(fn($p) => [
                'name' => $p->name,
                'price' => number_format($p->variants->min('price'), 0, ',', '.') . 'đ',
                'url' => route('customer.products.show', $p->slug),
                'image' => asset('storage/' . ($p->variants->first()->images->first()->image_url ?? ''))
            ])->take(3)
        ]);
    }

    protected function parseIntent($message)
    {
        $intent = ['type' => 'general', 'brand' => null, 'price_max' => null, 'features' => []];

        // Tìm thương hiệu
        $brands = Brand::where('is_active', true)->get();
        foreach ($brands as $brand) {
            if (Str::contains($message, mb_strtolower($brand->name))) {
                $intent['brand'] = $brand->id;
                $intent['type'] = 'recommendation';
            }
        }

        // Tìm giá (ví dụ: "dưới 10 triệu", "dưới 10tr", "dưới 10.000.000")
        if (preg_match('/dưới\s+(\d+)\s*(triệu|tr|tỷ|m)/u', $message, $matches)) {
            $value = (int)$matches[1];
            if (Str::contains($matches[2], ['triệu', 'tr', 'm'])) $value *= 1000000;
            $intent['price_max'] = $value;
            $intent['type'] = 'recommendation';
        }

        // Tìm tính năng
        if (Str::contains($message, ['chụp ảnh', 'camera', 'quay phim', 'hình ảnh'])) $intent['features'][] = 'camera';
        if (Str::contains($message, ['chơi game', 'gaming', 'hiệu năng', 'mạnh', 'pin'])) $intent['features'][] = 'performance';

        return $intent;
    }

    protected function findProducts($intent, $message)
    {
        $query = Product::where('status', 1)->whereNull('deleted_at');

        if ($intent['brand']) {
            $query->where('brand_id', $intent['brand']);
        }

        if ($intent['price_max']) {
            $query->whereHas('variants', function($q) use ($intent) {
                $q->where('price', '<=', $intent['price_max']);
            });
        }

        // Nếu có tính năng, có thể lọc theo mô tả hoặc specifications (giả định có cột specifications)
        if (in_array('camera', $intent['features'])) {
            $query->where(function($q) {
                $q->where('description', 'like', '%camera%')
                  ->orWhere('description', 'like', '%chụp ảnh%');
            });
        }

        if (in_array('performance', $intent['features'])) {
            $query->where(function($q) {
                $q->where('description', 'like', '%chip%')
                  ->orWhere('description', 'like', '%hiệu năng%')
                  ->orWhere('description', 'like', '%chơi game%');
            });
        }

        // Nếu không có intent rõ ràng, tìm theo từ khóa ngẫu nhiên trong tin nhắn
        if ($intent['type'] === 'general' && strlen($message) > 3) {
            $keywords = explode(' ', $message);
            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (strlen($kw) > 2) $q->orWhere('name', 'like', "%$kw%");
                }
            });
        }

        return $query->with(['variants.images'])->limit(5)->get();
    }

    protected function generateResponse($intent, $products)
    {
        if ($products->isEmpty()) {
            return "Xin lỗi, hiện tại tôi chưa tìm thấy sản phẩm nào phù hợp với yêu cầu của bạn. Bạn có thể thử mô tả chi tiết hơn được không? Ví dụ: 'iPhone dưới 20 triệu'.";
        }

        $count = $products->count();
        $res = "Dựa trên yêu cầu của bạn, tôi tìm thấy $count sản phẩm tuyệt vời phù hợp:\n\n";
        
        foreach ($products as $p) {
            $minPrice = number_format($p->variants->min('price'), 0, ',', '.') . 'đ';
            $res .= "• **{$p->name}**: Giá chỉ từ $minPrice\n";
        }

        $res .= "\nBạn có muốn tìm hiểu chi tiết về sản phẩm nào trong số này không?";
        
        return $res;
    }
}
