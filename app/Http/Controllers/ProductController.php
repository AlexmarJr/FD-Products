<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\ProductServices;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        protected ProductServices $service
    ) {}

    public function index(Request $request): Response
    {
        $query = Product::where('user_id', Auth::id());

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $searchLower = mb_strtolower($search);

            $query->where(function ($builder) use ($searchLower) {
                $builder->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        return Inertia::render('Products', [
            'products' => $query
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',
                Rule::unique('products')
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('user_id', Auth::id())
                            ->whereRaw('LOWER(name) = LOWER(?)', [$request->input('name')]);
                    }),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'supplier' => ['nullable', 'string', 'max:255'],
        ]);

        //Aplicação Solid - So a nivel de demonstração, mas pra inserts simples eu não usaria
        $this->service->create(
            $validated,
            Auth::id()
        );

        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',
                Rule::unique('products')
                    ->ignore($product->id)
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('user_id', Auth::id())
                            ->whereRaw('LOWER(name) = LOWER(?)', [$request->input('name')]);
                    }),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
        ]);
        
        $this->service->update(
            $product,
            $validated,
            Auth::id()
        );
        return redirect()->route('products.index');
    }

    public function destroy(Product $product)
    {
        //Dependendo do cenario um softdelete poderia ser mais adequade, mas ai vai de cada caso de uso e intepretação do LGPD tambem.
        //como é so um teste, vou so da um delete mesmo
        $product->delete();

        return redirect()->route('products.index');
    }

    public function generate()
    {
        $realProducts = [
            ['name' => 'iPhone 15 Pro', 'supplier' => 'Apple', 'cost' => 3500, 'sale' => 7999, 'qty' => 15],
            ['name' => 'Samsung Galaxy S24', 'supplier' => 'Samsung', 'cost' => 2800, 'sale' => 6500, 'qty' => 20],
            ['name' => 'MacBook Pro 16"', 'supplier' => 'Apple', 'cost' => 8000, 'sale' => 17999, 'qty' => 5],
            ['name' => 'iPad Air', 'supplier' => 'Apple', 'cost' => 2200, 'sale' => 5499, 'qty' => 12],
            ['name' => 'AirPods Pro', 'supplier' => 'Apple', 'cost' => 850, 'sale' => 1899, 'qty' => 50],
            ['name' => 'Sony WH-1000XM5', 'supplier' => 'Sony', 'cost' => 900, 'sale' => 2499, 'qty' => 18],
            ['name' => 'DJI Mini 3 Pro', 'supplier' => 'DJI', 'cost' => 2500, 'sale' => 5799, 'qty' => 8],
            ['name' => 'Canon EOS R6', 'supplier' => 'Canon', 'cost' => 6500, 'sale' => 14999, 'qty' => 6],
            ['name' => 'Kindle Paperwhite', 'supplier' => 'Amazon', 'cost' => 450, 'sale' => 999, 'qty' => 30],
            ['name' => 'Google Pixel 8 Pro', 'supplier' => 'Google', 'cost' => 2600, 'sale' => 6999, 'qty' => 14],
            ['name' => 'Tesla Model Y', 'supplier' => 'Tesla', 'cost' => 150000, 'sale' => 350000, 'qty' => 2],
            ['name' => 'Nest Hub Max', 'supplier' => 'Google', 'cost' => 600, 'sale' => 1299, 'qty' => 10],
            ['name' => 'PlayStation 5', 'supplier' => 'Sony', 'cost' => 2500, 'sale' => 4999, 'qty' => 12],
            ['name' => 'Xbox Series X', 'supplier' => 'Microsoft', 'cost' => 2300, 'sale' => 4999, 'qty' => 10],
            ['name' => 'Nintendo Switch OLED', 'supplier' => 'Nintendo', 'cost' => 1800, 'sale' => 3499, 'qty' => 16],
            ['name' => 'Meta Quest 3', 'supplier' => 'Meta', 'cost' => 1800, 'sale' => 3999, 'qty' => 8],
            ['name' => 'Oculus Rift S', 'supplier' => 'Meta', 'cost' => 1200, 'sale' => 2999, 'qty' => 6],
            ['name' => 'LG OLED TV 65"', 'supplier' => 'LG', 'cost' => 5000, 'sale' => 12999, 'qty' => 4],
            ['name' => 'Samsung 8K TV 75"', 'supplier' => 'Samsung', 'cost' => 8000, 'sale' => 19999, 'qty' => 3],
            ['name' => 'Sonos Arc', 'supplier' => 'Sonos', 'cost' => 1200, 'sale' => 2999, 'qty' => 11],
            ['name' => 'Dyson V15 Detect', 'supplier' => 'Dyson', 'cost' => 2200, 'sale' => 5499, 'qty' => 7],
            ['name' => 'iRobot Roomba j7+', 'supplier' => 'iRobot', 'cost' => 1500, 'sale' => 3499, 'qty' => 9],
            ['name' => 'Nespresso VertuoPlus', 'supplier' => 'Nespresso', 'cost' => 400, 'sale' => 999, 'qty' => 25],
            ['name' => 'DeLonghi Primadonna XS', 'supplier' => 'DeLonghi', 'cost' => 800, 'sale' => 1999, 'qty' => 8],
            ['name' => 'Instant Pot Ultra', 'supplier' => 'Instant Brands', 'cost' => 300, 'sale' => 799, 'qty' => 22],
            ['name' => 'GoPro Hero 12', 'supplier' => 'GoPro', 'cost' => 800, 'sale' => 1999, 'qty' => 14],
            ['name' => 'Garmin fénix 7X', 'supplier' => 'Garmin', 'cost' => 2200, 'sale' => 4999, 'qty' => 6],
            ['name' => 'Apple Watch Ultra', 'supplier' => 'Apple', 'cost' => 1500, 'sale' => 3499, 'qty' => 18],
            ['name' => 'Fitbit Charge 6', 'supplier' => 'Google', 'cost' => 400, 'sale' => 999, 'qty' => 28],
            ['name' => 'Anker PowerCore Ultra', 'supplier' => 'Anker', 'cost' => 120, 'sale' => 299, 'qty' => 60],
        ];

        foreach ($realProducts as $data) {
            $exists = Product::where('user_id', Auth::id())
                ->whereRaw('LOWER(name) = LOWER(?)', [$data['name']])
                ->exists();

            if (!$exists) {
                Product::create([
                    'user_id' => Auth::id(),
                    'name' => $data['name'],
                    'description' => "Produto de qualidade fornecido por {$data['supplier']}.",
                    'status' => 'ativo',
                    'quantity' => $data['qty'],
                    'cost_price' => $data['cost'],
                    'sale_price' => $data['sale'],
                    'supplier' => $data['supplier'],
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', "Produtos gerados com sucesso!");
    }
}