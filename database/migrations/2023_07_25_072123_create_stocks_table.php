<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expired_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}
