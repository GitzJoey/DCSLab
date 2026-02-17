<?php

namespace App\Validation\StockAdjustment;

use App\Rules\ExistsForCompany;

class StockAdjustmentInProductRules
{
    public const FIELD_QTY = 'qty';

    public const FIELD_PRODUCT_UNIT_ID = 'product_unit_id';

    public const FIELD_PRODUCT_UNIT_CONVERSION_VALUE = 'product_unit_conversion_value';

    public const FIELD_PRODUCT_UNIT_COGS = 'product_unit_cogs';

    public const FIELD_REMARKS = 'remarks';

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(int $companyId): array
    {
        return [
            self::FIELD_QTY => ['required', 'numeric', 'min:1'],
            self::FIELD_PRODUCT_UNIT_ID => ['required', 'integer', new ExistsForCompany('product_units', $companyId)],
            self::FIELD_PRODUCT_UNIT_CONVERSION_VALUE => ['required', 'numeric', 'min:1'],
            self::FIELD_PRODUCT_UNIT_COGS => ['required', 'numeric', 'min:0'],
            self::FIELD_REMARKS => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Memetakan aturan dasar ke nama field lain (misal nested array di Request).
     *
     * Aturan yang diaplikasikan:
     * - qty: required, numeric, min:1
     * - product_unit_id: required, integer, ExistsForCompany(product_units)
     * - product_unit_conversion_value: required, numeric, min:1
     * - product_unit_cogs: required, numeric, min:0
     * - remarks: nullable, string, max:255
     *
     * @return array<string, array<int, mixed>>
     */
    public static function mapToFieldNames(
        int $companyId,
        string $qtyField,
        string $productUnitIdField,
        string $productUnitConversionValueField,
        string $productUnitCogsField,
        string $remarksField,
    ): array {
        $base = self::rules($companyId);

        return [
            $qtyField => $base[self::FIELD_QTY],
            $productUnitIdField => $base[self::FIELD_PRODUCT_UNIT_ID],
            $productUnitConversionValueField => $base[self::FIELD_PRODUCT_UNIT_CONVERSION_VALUE],
            $productUnitCogsField => $base[self::FIELD_PRODUCT_UNIT_COGS],
            $remarksField => $base[self::FIELD_REMARKS],
        ];
    }
}
