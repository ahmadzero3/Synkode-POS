<?php

namespace App\Enums;

enum ItemTransactionUniqueCode: string
{
/**
     * Applied in tables
     * 1. item_transaction
     * 2. item_batch_transaction
     * */
    case ITEM_OPENING                   = 'ITEM_OPENING';

    case PURCHASE_ORDER                 = 'PURCHASE_ORDER';
    case PURCHASE                       = 'PURCHASE';
    case PURCHASE_RETURN                = 'PURCHASE_RETURN';

    case SALE_ORDER                     = 'SALE_ORDER';
    case SALE                           = 'SALE';
    case SALE_RETURN                    = 'SALE_RETURN';
    case STOCK_TRANSFER                 = 'STOCK_TRANSFER';
    case STOCK_RECEIVE                  = 'STOCK_RECEIVE';
    case QUOTATION                      = 'QUOTATION';
    case STOCK_ADJUSTMENT               = 'STOCK_ADJUSTMENT';
    case STOCK_ADJUSTMENT_INCREASE        = 'STOCK_ADJUSTMENT_INCREASE';
    case STOCK_ADJUSTMENT_DECREASE        = 'STOCK_ADJUSTMENT_DECREASE';
}
