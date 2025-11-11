<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Accounts\AccountGroup;
use App\Models\Accounts\Account;
use App\Enums\AccountUniqueCode;

class AccountGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->insertNestedGroups($this->mainGroupData());

        $this->insertAccoubtsWithGroupId($this->accountData());
    }

    private function insertAccoubtsWithGroupId($accounts, &$createdAccounts = [])
    {
        $getAllAccountGroups = AccountGroup::all();
        foreach ($accounts as $account) {

            // Find the matching group using firstWhere() or where() with a default
            $matchingGroup = $getAllAccountGroups->firstWhere('unique_code', $account['account_group']);

            // Handle cases where no matching group is found
            if (!$matchingGroup) {
                // Throw an exception, log a warning, or handle it gracefully
                return "Account group with unique code '" . $account['account_group'] . "' not found.";
            }

            $createdGroup = Account::create(
                [
                    'group_id'   =>  $matchingGroup->id,
                    'name'        => $account['name'],
                    'description' => $account['description'],
                    //'balance'     => $account['balance'],
                    'unique_code' => $account['unique_code'],
                    'is_deletable' => 0, //Restrict it from delete
                ]
            );
        }
    }

    private function insertNestedGroups($groups, &$createdGroups = [])
    {
        foreach ($groups as $groupData) {


            $createdGroup = AccountGroup::create(
                [
                    'parent_id'   => $groupData['parent_id'],
                    'name'        => $groupData['name'],
                    'description' => $groupData['description'],
                    'balance'     => $groupData['balance'],
                    'unique_code' => $groupData['unique_code'],
                    'is_deletable' => 0, //Restrict it from delete
                ]
            );

            $createdGroups[$createdGroup->id] = $createdGroup->id;

            if (isset($groupData['children']) && !empty($groupData['children'])) {
                // Dynamically add parent_id to child data (using reference)
                foreach ($groupData['children'] as &$childData) {
                    $childData['parent_id'] = $createdGroup->id;
                }
                $this->insertNestedGroups($groupData['children'], $createdGroups);
            }
        }
    }

    private function mainGroupData()
    {
        $mainGroupData = [
            [
                'parent_id'     => 0,
                'name'          => 'Assets',
                'description'   => 'Assets are anything valuable that your company owns, whether it’s equipment, land, buildings, or intellectual property.',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::ASSETS->value,
                'children'      =>  [
                    [
                        'name'          => 'Fixed Assets',
                        'description'   => 'Children of Assets',
                        'balance'       => 0,
                        'unique_code'   => AccountUniqueCode::FIXED_ASSETS->value,
                        'children'      =>  [],
                    ],
                    [
                        'name'          => 'Current Assets',
                        'description'   => 'Children of Assets',
                        'balance'       => 0,
                        'unique_code'   => AccountUniqueCode::CURRENT_ASSETS->value,
                        'children'      =>  [
                            [
                                'name'          => 'Sundry Debtors',
                                'description'   => 'Children of Current Assets',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::SUNDRY_DEBTORS->value,
                                'children'      =>  [],
                            ],
                            [
                                'name'          => 'Cash Account',
                                'description'   => 'Children of Current Assets',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::CASH_ACCOUNT->value,
                                'children'      =>  [],
                            ],
                            /*[
                                            'name'          => 'Other Current Assets',
                                            'description'   => 'Children of Current Assets',
                                            'balance'       => 0,
                                            'unique_code'   => AccountUniqueCode::OTHER_CASH_ACCOUNT->value,
                                            'children'      =>  [],
                                        ],*/
                            [
                                'name'          => 'Bank Accounts',
                                'description'   => 'Children of Current Assets',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::BANK_ACCOUNT->value,
                                'children'      =>  [],
                            ],
                            [
                                'name'          => 'Input Duties & Taxes',
                                'description'   => 'Children of Current Assets',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::INPUT_DUTIES_AND_TAXES->value,
                                'children'      =>  [
                                    [
                                        'name'          => 'Input Tax',
                                        'description'   => 'Children of Current Assets',
                                        'balance'       => 0,
                                        'unique_code'   => AccountUniqueCode::INPUT_TAX->value,
                                        'children'      =>  [],
                                    ],
                                ],
                            ],
                            [
                                'name'          => 'Other Current Assets',
                                'description'   => 'Children of Current Assets',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::OTHER_CURRENT_ASSETS->value,
                                'children'      => []
                            ],
                        ],
                    ],
                ],
            ],
            [
                'parent_id'     => 0,
                'name'          => 'Equities & Liabilities',
                'description'   => 'Your liabilities are any debts your company has, whether it’s bank loans, mortgages, unpaid bills, IOUs, or any other sum of money that you owe someone else. If you’ve promised to pay someone in the future, and haven’t paid them yet, that’s a liability.',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::EQUITIES_AND_LIABILITIES->value,
                'children'      =>  [
                    [
                        'name'          => 'Capital Account',
                        'description'   => 'Child of Equities & Liabilities',
                        'balance'       => 0,
                        'unique_code'   => AccountUniqueCode::CAPITAL_ACCOUNT->value,
                        'children'      =>  [
                            [
                                'name'          => 'Owner\'s Equity',
                                'description'   => 'Child of Capital Account',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::OWNERS_EQUITY->value,
                                'children'      =>  [
                                    [
                                        'name'          => 'Opening Balance Equity',
                                        'description'   => 'Owner\'s Equity',
                                        'balance'       => 0,
                                        'unique_code'   => AccountUniqueCode::OPENING_BALANCE_EQUITY->value,
                                        'children'      =>  [],
                                    ],
                                ],
                            ],
                            [
                                'name'          => 'Reserves & Surplus',
                                'description'   => 'Child of Capital Account',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::RESERVES_AND_SURPLUS->value,
                                'children'      =>  [],
                            ],
                        ],
                    ],
                    [
                        'name'          => 'Long-term Liabilities',
                        'description'   => 'Child of Equities & Liabilities',
                        'balance'       => 0,
                        'unique_code'   => AccountUniqueCode::LONG_TERM_EQUITIES_AND_LIABILITIES->value,
                        'children'      =>  [],
                    ],
                    [
                        'name'          => 'Current Liabilities',
                        'description'   => 'Child of Equities & Liabilities',
                        'balance'       => 0,
                        'unique_code'   => AccountUniqueCode::CURRENT_LIABILITIES->value,
                        'children'      =>  [
                            [
                                'name'          => 'Other Current Liabilities',
                                'description'   => 'Child of Equities & Liabilities',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::OTHER_CURRENT_LIABILITIES->value,
                                'children'      =>  [],
                            ],

                            [
                                'name'          => 'Sundry Creditors',
                                'description'   => 'Child of Equities & Liabilities',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::SUNDRY_CREDITORS->value,
                                'children'      =>  [],
                            ],
                            [
                                'name'          => 'Outward Duties & Taxes',
                                'description'   => 'Child of Equities & Liabilities',
                                'balance'       => 0,
                                'unique_code'   => AccountUniqueCode::OUTWARD_DUTIES_AND_TAXES->value,
                                'children'      =>  [
                                    [
                                        'name'          => 'Output Tax',
                                        'description'   => 'Child of Outward Duties & Taxes',
                                        'balance'       => 0,
                                        'unique_code'   => AccountUniqueCode::OUTPUT_TAX->value,
                                        'children'      =>  [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'parent_id'     => 0,
                'name'          => 'Expenses',
                'description'   => '',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::EXPENSES->value,
                'children'      =>  [
                    [
                        'name'          => 'Purchase Accounts',
                        'description'   => '',
                        'balance'       => 0,
                        'unique_code'   => AccountUniqueCode::PURCHASE_ACCOUNTS->value,
                        'children'      =>  [],
                    ],
                    [
                        'name'          => 'Direct Expenses',
                        'description'   => '',
                        'balance'       => 0,
                        'unique_code'   => AccountUniqueCode::DIRECT_EXPENSES->value,
                        'children'      =>  [],
                    ],
                    [
                        'name'          => 'Indirect Expenses',
                        'description'   => '',
                        'balance'       => 0,
                        'unique_code'   => AccountUniqueCode::INDIRECT_EXPENSES->value,
                        'children'      =>  [],
                    ],
                ],
            ],

        ];

        return $mainGroupData;
    } //Main group Data

    private function accountData()
    {
        $mainGroupData = [
            [
                'account_group' => AccountUniqueCode::CURRENT_ASSETS->value, //Used for where condition of AccountGroup Model
                'name'          => 'Stock-in-Hand',
                'description'   => 'Total Current Stock Value',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::STOCK_IN_HAND->value,
            ],
            [
                'account_group' => AccountUniqueCode::INPUT_TAX->value, //Used for where condition of AccountGroup Model
                'name'          => 'Input CCGT',
                'description'   => 'Records Input Tax/GST',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::INPUT_CGST->value,
            ],
            [
                'account_group' => AccountUniqueCode::INPUT_TAX->value, //Used for where condition of AccountGroup Model
                'name'          => 'Input CCGT',
                'description'   => 'Records Input Tax/GST',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::INPUT_SGST->value,
            ],
            [
                'account_group' => AccountUniqueCode::INPUT_TAX->value, //Used for where condition of AccountGroup Model
                'name'          => 'Input Tax All',
                'description'   => 'Records Input Taxes',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::INPUT_TAX_ALL->value,
            ],
            [
                'account_group' => AccountUniqueCode::OUTPUT_TAX->value, //Used for where condition of AccountGroup Model
                'name'          => 'Output CCGT',
                'description'   => 'Records Output Tax/GST',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::OUTPUT_CGST->value,
            ],
            [
                'account_group' => AccountUniqueCode::OUTPUT_TAX->value, //Used for where condition of AccountGroup Model
                'name'          => 'Output CCGT',
                'description'   => 'Records Output Tax/GST',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::OUTPUT_SGST->value,
            ],
            [
                'account_group' => AccountUniqueCode::OUTPUT_TAX->value, //Used for where condition of AccountGroup Model
                'name'          => 'Output Tax All',
                'description'   => 'Records Output Tax All',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::OUTPUT_TAX_ALL->value,
            ],
            [
                'account_group' => AccountUniqueCode::CASH_ACCOUNT->value, //Used for where condition of AccountGroup Model
                'name'          => 'Cash In Hand',
                'description'   => 'Children of Cash Account',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::CASH_IN_HAND->value,
            ],
            /*[
                    'account_group' => AccountUniqueCode::DIRECT_EXPENSES->value,//Used for where condition of AccountGroup Model
                    'name'          => 'Direct Expense Constraint',
                    'description'   => 'Children of Direct Expenses',
                    'balance'       => 0,
                    'unique_code'   => AccountUniqueCode::DIRECT_EXPENSE_CONSTRAINT->value,
                ],
                [
                    'account_group' => AccountUniqueCode::INDIRECT_EXPENSES->value,//Used for where condition of AccountGroup Model
                    'name'          => 'Indirect Expense Constraint',
                    'description'   => 'Children of In-Direct Expenses',
                    'balance'       => 0,
                    'unique_code'   => AccountUniqueCode::INDIRECT_EXPENSE_CONSTRAINT->value,
                ],*/
            [
                'account_group' => AccountUniqueCode::OTHER_CURRENT_LIABILITIES->value, //Used for where condition of AccountGroup Model
                'name'          => 'Unwithdrawn Cheques',
                'description'   => 'Children of Other Current Liabilities',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::UNWITHDRAWN_CHEQUES->value,
            ],
            [
                'account_group' => AccountUniqueCode::RESERVES_AND_SURPLUS->value, //Used for where condition of AccountGroup Model
                'name'          => 'Net Income(Profit)',
                'description'   => 'Children of Reserves & Surplus',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::NET_INCOME_OR_PROFIT->value,
            ],
            [
                'account_group' => AccountUniqueCode::OPENING_BALANCE_EQUITY->value, //Used for where condition of AccountGroup Model
                'name'          => 'Opening Stock Balance',
                'description'   => 'Children of Opening Balance Equity',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::OPENING_STOCK_BALANCE->value,
            ],
            [
                'account_group' => AccountUniqueCode::SUNDRY_DEBTORS->value, //Used for where condition of AccountGroup Model
                'name'          => 'Sundry Debtors List',
                'description'   => 'Children of Sundry Debtors',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::SUNDRY_DEBTORS_LIST->value,
            ],
            [
                'account_group' => AccountUniqueCode::SUNDRY_CREDITORS->value, //Used for where condition of AccountGroup Model
                'name'          => 'Sundry Creditors List',
                'description'   => 'Children of Sundry Creditors',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::SUNDRY_CREDITORS_LIST->value,
            ],
            [
                'account_group' => AccountUniqueCode::SUNDRY_CREDITORS->value, //Used for where condition of AccountGroup Model
                'name'          => 'Party Opening Balance',
                'description'   => 'Children of Opening Balance Equity',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::PARTY_OPENING_BALANCE->value,
            ],
            [
                'account_group' => AccountUniqueCode::OTHER_CURRENT_ASSETS->value, //Used for where condition of AccountGroup Model
                'name'          => 'Advance Paid for Purchase Order',
                'description'   => 'Children of Other Current Assets',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::ADVANCE_PAID_FOR_PURCHASE_ORDER->value,
            ],
            [
                'account_group' => AccountUniqueCode::PURCHASE_ACCOUNTS->value, //Used for where condition of AccountGroup Model
                'name'          => 'Purchases',
                'description'   => 'Children of Purchase Accounts',
                'balance'       => 0,
                'unique_code'   => AccountUniqueCode::PURCHASES->value,
            ],
        ];

        return $mainGroupData;
    } //accounts Data
}
