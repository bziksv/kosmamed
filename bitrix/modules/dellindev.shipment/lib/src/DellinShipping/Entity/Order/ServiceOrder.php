<?php
/**
 * —ервисы описывающие переиспользуемые зависимости дл€ заказов.
 * –еализовано через автономные методы в том числе и сетевые.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */

namespace DellinShipping\Entity\Order;


use DellinShipping\NetworkService;

class ServiceOrder
{

    /**
     * Id типов упаковки (дл€ калькул€тора)
     * @var array
     */
    public static $packing_types_id = [
        'box' => '0x951783203a254a05473c43733c20fe72',
        'hard' => '0x838FC70BAEB49B564426B45B1D216C15',
        'additional' => '0x9A7F11408F4957D7494570820FCF4549',
        'bubble' => '0xA8B42AC5EC921A4D43C0B702C3F1C109',
        'bag' => '0xAD22189D098FB9B84EEC0043196370D6',
        'pallet' => '0xBAA65B894F477A964D70A4D97EC280BE',
        'car_glass' => '0x9dd8901b0ecef10c11e8ed001199bf6f',
        'car_parts' => '0x9dd8901b0ecef10c11e8ed001199bf70',
        'complex_pallet' =>'0x9dd8901b0ecef10c11e8ed001199bf71',
        'complex_hard' => '0x9dd8901b0ecef10c11e8ed001199bf6e',
    ];

    /**
     * Id типов упаковки (дл€ отправки за€вок)
     * @var array
     */
    public static $request_packages_id = [
        'box'=>'0x82750921BC8128924D74F982DD961379',
        'hard' => '0xA6A7BD2BF950E67F4B2CF7CC3A97C111',
        'additional' => '0xAE2EEA993230333043E719D4965D5D31',
        'bubble' => '0xB5FF5BC18E642C354556B93D7FBCDE2F',
        'bag' => '0x947845D9BDC69EFA49630D8C080C4FBE',
        'pallet' => '0xA0A820F33B2F93FE44C8058B65C77D0F',
        'car_glass' => '0xad97901b0ecef0f211e889fcf4624fed',
        'car_parts' => '0xad97901b0ecef0f211e889fcf4624fea',
        'complex_pallet' => '0xad97901b0ecef0f211e889fcf4624feb',
        'complex_hard' => '0xad97901b0ecef0f211e889fcf4624fec',
    ];






}