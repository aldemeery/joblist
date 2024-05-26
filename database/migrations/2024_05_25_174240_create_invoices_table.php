<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('occupancy_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->date('date');
            $table->unsignedInteger('net_terms');
            $table->date('due_date');
            $table->string('issuer_address_line_one');
            $table->string('issuer_address_line_two')->nullable();
            $table->string('issuer_address_line_three')->nullable();
            $table->string('issuer_address_line_four')->nullable();
            $table->string('client_address_line_one');
            $table->string('client_address_line_two')->nullable();
            $table->string('client_address_line_three')->nullable();
            $table->string('client_address_line_four')->nullable();
            $table->decimal('tax', 4, 2)->default(0)->comment('Percentage');
            $table->decimal('discount', 8, 2)->default(0)->comment('Amount');
            $table->text('payment_details');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['occupancy_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
