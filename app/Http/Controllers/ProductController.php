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
}