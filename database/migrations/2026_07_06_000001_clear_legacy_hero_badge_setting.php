<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'hero_badge')
            ->whereIn('value', [
                'Сейчас открыта запись',
                'Now booking / editorial work',
                'Open for commissions / 2026',
                'РЎРµР№С‡Р°СЃ РѕС‚РєСЂС‹С‚Р° Р·Р°РїРёСЃСЊ',
                'Р РЋР ВµР в„–РЎвЂЎР В°РЎРѓ Р С•РЎвЂљР С”РЎР‚РЎвЂ№РЎвЂљР В° Р В·Р В°Р С—Р С‘РЎРѓРЎРЉ',
            ])
            ->update(['value' => '']);
    }

    public function down(): void
    {
        //
    }
};
