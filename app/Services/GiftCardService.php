<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\I18n;
use Database\Database;

/**
 * Gift cards: purchase (as cart lines), issuing on checkout, activation on payment
 * and redemption through the cart's promo-code field.
 */
class GiftCardService
{
    public const MIN_AMOUNT = 50;
    public const MAX_AMOUNT = 5000;
    public const PRESET_AMOUNTS = [100, 200, 300, 500, 1000];

    /** Session cart entry => cart summary line */
    public static function cartLine(string $key, array $entry): array
    {
        $isAr = I18n::getLocale() !== 'en';
        $amount = (float)($entry['amount'] ?? 0);
        $qty = max(1, min(10, (int)($entry['qty'] ?? 1)));
        $printed = ($entry['card_type'] ?? 'digital') === 'printed';
        $typeLabel = $isAr ? ($printed ? 'بطاقة مطبوعة' : 'بطاقة رقمية') : ($printed ? 'Printed card' : 'Digital card');
        $to = trim((string)($entry['recipient_name'] ?? ''));
        $sizeName = $typeLabel . ($to !== '' ? ($isAr ? ' · إلى ' : ' · To ') . $to : '');
        $total = $amount * $qty;

        return [
            'key' => $key,
            'variant_id' => null,
            'product_id' => null,
            'product_name' => $isAr ? 'بطاقة إهداء تمرنا' : 'Tamrna Gift Card',
            'size_name' => $sizeName,
            'sku' => 'GIFT-CARD',
            'image' => 'assets/images/home/gifting.webp',
            'unit_price' => $amount,
            'unit_price_formatted' => number_format($amount, 2) . ' ' . currency(),
            'quantity' => $qty,
            'max_stock' => 10,
            'is_preorder' => false,
            'is_fragile' => false,
            'is_gift_card' => true,
            'gift' => [
                'card_type' => $printed ? 'printed' : 'digital',
                'amount' => $amount,
                'sender_name' => (string)($entry['sender_name'] ?? ''),
                'sender_phone' => (string)($entry['sender_phone'] ?? ''),
                'recipient_name' => $to,
                'recipient_phone' => (string)($entry['recipient_phone'] ?? ''),
                'recipient_email' => (string)($entry['recipient_email'] ?? ''),
                'message' => (string)($entry['message'] ?? ''),
            ],
            'subtotal' => $total,
            'subtotal_formatted' => number_format($total, 2) . ' ' . currency(),
        ];
    }

    /** Validate purchase input; returns [entry, error] */
    public static function buildEntry(array $in): array
    {
        $isAr = I18n::getLocale() !== 'en';
        $amount = (float)($in['amount'] ?? 0);
        $sender = trim((string)($in['sender_name'] ?? ''));
        $recipient = trim((string)($in['recipient_name'] ?? ''));
        $recipientPhone = trim((string)($in['recipient_phone'] ?? ''));
        $message = trim((string)($in['message'] ?? ''));

        if ($amount < self::MIN_AMOUNT || $amount > self::MAX_AMOUNT) {
            return [null, $isAr
                ? 'قيمة البطاقة يجب أن تكون بين ' . self::MIN_AMOUNT . ' و' . self::MAX_AMOUNT . ' ' . currency()
                : 'Card value must be between ' . self::MIN_AMOUNT . ' and ' . self::MAX_AMOUNT . ' ' . currency()];
        }
        if (mb_strlen($sender) < 2 || mb_strlen($recipient) < 2) {
            return [null, $isAr ? 'يرجى كتابة اسم المُرسل واسم المُهدى إليه' : 'Please enter the sender and recipient names'];
        }
        if ($recipientPhone !== '' && !preg_match('/^(\+?966|0)?5\d{8}$/', preg_replace('/[\s-]+/', '', $recipientPhone))) {
            return [null, $isAr ? 'رقم جوال المستلم غير صحيح (مثال: 05xxxxxxxx)' : 'Recipient mobile is not valid (e.g. 05xxxxxxxx)'];
        }

        return [[
            'type' => 'gift_card',
            'card_type' => ($in['card_type'] ?? '') === 'printed' ? 'printed' : 'digital',
            'amount' => round($amount, 2),
            'sender_name' => mb_substr($sender, 0, 150),
            'sender_phone' => mb_substr(trim((string)($in['sender_phone'] ?? '')), 0, 50),
            'recipient_name' => mb_substr($recipient, 0, 150),
            'recipient_phone' => mb_substr($recipientPhone, 0, 50),
            'recipient_email' => mb_substr(trim((string)($in['recipient_email'] ?? '')), 0, 150),
            'message' => mb_substr($message, 0, 180),
            'qty' => 1,
        ], null];
    }

    /** Active gift card that can be redeemed with this code (or null) */
    public static function findRedeemable(string $code): ?array
    {
        $row = Database::fetchOne("SELECT * FROM gift_cards WHERE code = ? AND status = 'active' AND balance > 0", [strtoupper(trim($code))]);
        return $row ?: null;
    }

    public static function generateCode(): string
    {
        do {
            $code = 'TUM-GIFT-' . strtoupper(bin2hex(random_bytes(3)));
            $exists = Database::fetchOne("SELECT id FROM gift_cards WHERE code = ?", [$code]);
        } while ($exists);
        return $code;
    }

    /** Create pending gift card rows for a purchased cart line (one per quantity) */
    public static function issueForOrder(\PDO $pdo, int $orderId, array $item): array
    {
        $g = $item['gift'] ?? [];
        $codes = [];
        $stmt = $pdo->prepare("
            INSERT INTO gift_cards (code, card_type, amount, balance, sender_name, recipient_name, recipient_phone, recipient_email, message, status, order_id, sender_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)
        ");
        for ($i = 0; $i < max(1, (int)$item['quantity']); $i++) {
            $code = self::generateCode();
            $stmt->execute([
                $code, $g['card_type'] ?? 'digital', $g['amount'] ?? 0, $g['amount'] ?? 0,
                $g['sender_name'] ?? '', $g['recipient_name'] ?? '', $g['recipient_phone'] ?? null,
                $g['recipient_email'] ?? null, $g['message'] ?? null, $orderId, $g['sender_phone'] ?? null,
            ]);
            $codes[] = $code;
        }
        return $codes;
    }

    /** Pending cards bought in this order become usable once the order is paid */
    public static function activateForOrder(int $orderId): void
    {
        Database::execute("UPDATE gift_cards SET status = 'active' WHERE order_id = ? AND status = 'pending'", [$orderId]);
    }

    /** Deduct a redeemed amount; the card is marked used once its balance reaches zero */
    public static function consume(\PDO $pdo, string $code, float $amount): void
    {
        // MySQL applies SET assignments left to right: compute status from the pre-update balance first
        $stmt = $pdo->prepare("
            UPDATE gift_cards
            SET status = CASE WHEN balance - ? <= 0.0001 THEN 'used' ELSE status END,
                balance = GREATEST(0, balance - ?)
            WHERE code = ? AND status = 'active'
        ");
        $stmt->execute([$amount, $amount, $code]);
    }
}
