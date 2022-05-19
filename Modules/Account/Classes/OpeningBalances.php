<?php

namespace Modules\Account\Classes;

use Modules\Account\Classes\Reports\TrialBalance;

use Modules\Account\Classes\People;
use Modules\Account\Classes\TaxAgencies;
use Modules\Account\Classes\LedgerAccounts;
use Modules\Account\Classes\Reports;


use Illuminate\Support\Facades\DB;

class OpeningBalances
{

    /**
     * Get all opening_balances
     *
     * @param array $args Data Filter
     *
     * @return mixed
     */
    public function getAllOpeningBalances($args = [])
    {


        $defaults = [
            'number'  => 20,
            'offset'  => 0,
            'orderby' => 'id',
            'order'   => 'DESC',
            'count'   => false,
            's'       => '',
        ];

        $args = array_merge($defaults, $args);

        $where = '';
        $limit = '';

        if (!empty($args['start_date'])) {
            $where .= "WHERE opening_balance.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
        }

        if ('-1' === $args['number']) {
            $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
        }

        $sql = 'SELECT';

        if ($args['count']) {
            $sql .= ' COUNT( DISTINCT opening_balance.id ) as total_number';
        } else {
            $sql .= ' *';
        }

        $sql .= " FROM account_opening_balance AS opening_balance LEFT JOIN account_financial_year AS financial_year";
        $sql .= " ON opening_balance.financial_year_id = financial_year.id {$where} GROUP BY financial_year.name ORDER BY financial_year.{$args['orderby']} {$args['order']} {$limit}";

        if ($args['count']) {
            return DB::scalar($sql);
        }

        return DB::select($sql);
    }

    /**
     * Get opening_balances of a year
     *
     * @param int $year_id Year Id
     *
     * @return mixed
     */
    public function getOpeningBalance($year_id)
    {


        $rows = DB::select(
            "SELECT ob.id, ob.financial_year_id, ob.ledger_id, ledger.name, ob.chart_id, ob.debit, ob.credit FROM account_opening_balance as ob LEFT JOIN account_ledger as ledger ON ledger.id = ob.ledger_id WHERE financial_year_id = %d AND ob.type = 'ledger'",
            [$year_id]
        );

        return $rows;
    }

    /**
     * Get virtual accounts of a year
     *
     * @param int $year_id Year Id
     *
     * @return mixed
     */
    public function getVirtualAcct($year_id)
    {


        $rows = DB::select(
            "SELECT ob.id, ob.financial_year_id, ob.ledger_id, ob.type, ob.debit, ob.credit
            FROM account_opening_balance as ob WHERE financial_year_id = %d AND ob.type <> 'ledger'",
            [$year_id]
        );

        $rows = $this->getOpbInvoiceAccountDetails($year_id);

        return $rows;
    }

    /**
     * Insert opening_balance data
     *
     * @param array $data Data Filter
     *
     * @return mixed
     */
    public function insertOpeningBalance($data)
    {


        $created_by         = auth()->user()->id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $created_by;

        try {
            DB::beginTransaction();

            $opening_balance_data = $this->getFormattedOpeningBalanceData($data);

            $items = $opening_balance_data['ledgers'];

            $ledgers = [];

            foreach ($items as $item) {
                $ledgers = array_merge($ledgers, $item);
            }

            $year_id = $opening_balance_data['year'];

            DB::delete("DELETE FROM account_opening_balance WHERE financial_year_id = %d", [$year_id]);

            foreach ($ledgers as $ledger) {
                if ((isset($ledger['debit']) && (float) $ledger['debit'] > 0) || (isset($ledger['credit']) && (float) $ledger['credit'] > 0)) {
                    DB::table('account_opening_balance')
                        ->insert(
                            [
                                'financial_year_id' => $year_id,
                                'ledger_id'         => $ledger['ledger_id'],
                                'chart_id'          => $ledger['chart_id'],
                                'type'              => 'ledger',
                                'debit'             => isset($ledger['debit']) ? $ledger['debit'] : 0,
                                'credit'            => isset($ledger['credit']) ? $ledger['credit'] : 0,
                                'created_at'        => $opening_balance_data['created_at'],
                                'created_by'        => $opening_balance_data['created_by'],
                                'updated_at'        => $opening_balance_data['updated_at'],
                                'updated_by'        => $opening_balance_data['updated_by'],
                            ]
                        );
                }
            }

            $this->insertObVirAccounts($opening_balance_data, $year_id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            config('kernel.messageBag')->add('opening_balance-exception', $e->getMessage());
            return;
        }

        return $this->getOpeningBalance($year_id);
    }

    /**
     * Insert virtual accounts data
     *
     * @param array $data    Data Filter
     * @param int   $year_id Year Id
     *
     * @return void
     */
    public function insertObVirAccounts($data, $year_id)
    {


        if (!empty($data['acct_rec'])) {
            foreach ($data['acct_rec'] as $acct_rec) {
                DB::table('account_opening_balance')
                    ->insert(
                        [
                            'financial_year_id' => $year_id,
                            'ledger_id'         => $acct_rec['people']['id'],
                            'type'              => 'people',
                            'debit'             => $acct_rec['debit'],
                            'credit'            => 0,
                            'created_at'        => $data['created_at'],
                            'created_by'        => $data['created_by'],
                            'updated_at'        => $data['updated_at'],
                            'updated_by'        => $data['updated_by'],
                        ]
                    );
            }
        }

        if (!empty($data['acct_pay'])) {
            foreach ($data['acct_pay'] as $acct_pay) {
                DB::table('account_opening_balance')
                    ->insert(
                        [
                            'financial_year_id' => $year_id,
                            'ledger_id'         => $acct_pay['people']['id'],
                            'type'              => 'people',
                            'debit'             => 0,
                            'credit'            => $acct_pay['credit'],
                            'created_at'        => $data['created_at'],
                            'created_by'        => $data['created_by'],
                            'updated_at'        => $data['updated_at'],
                            'updated_by'        => $data['updated_by'],
                        ]
                    );
            }
        }

        if (!empty($data['tax_pay'])) {
            foreach ($data['tax_pay'] as $tax_pay) {
                DB::table('account_opening_balance')
                    ->insert(
                        [
                            'financial_year_id' => $year_id,
                            'ledger_id'         => $tax_pay['agency']['id'],
                            'type'              => 'tax_agency',
                            'debit'             => 0,
                            'credit'            => $tax_pay['credit'],
                            'created_at'        => $data['created_at'],
                            'created_by'        => $data['created_by'],
                            'updated_at'        => $data['updated_at'],
                            'updated_by'        => $data['updated_by'],
                        ]
                    );
            }
        }
    }

    /**
     * Get formatted opening_balance data
     *
     * @param array $data Data Filter
     *
     * @return mixed
     */
    public function getFormattedOpeningBalanceData($data)
    {
        $opening_balance_data = [];

        $opening_balance_data['year']         = isset($data['year']) ? $data['year'] : '';
        $opening_balance_data['ledgers']      = isset($data['ledgers']) ? $data['ledgers'] : [];
        $opening_balance_data['descriptions'] = isset($data['descriptions']) ? $data['descriptions'] : '';
        $opening_balance_data['amount']       = isset($data['amount']) ? $data['amount'] : '';
        $opening_balance_data['acct_pay']     = isset($data['acct_pay']) ? $data['acct_pay'] : [];
        $opening_balance_data['acct_rec']     = isset($data['acct_rec']) ? $data['acct_rec'] : [];
        $opening_balance_data['tax_pay']      = isset($data['tax_pay']) ? $data['tax_pay'] : [];
        $opening_balance_data['created_at']   = isset($data['created_at']) ? $data['created_at'] : '';
        $opening_balance_data['created_by']   = isset($data['created_by']) ? $data['created_by'] : '';
        $opening_balance_data['updated_at']   = isset($data['updated_at']) ? $data['updated_at'] : '';
        $opening_balance_data['updated_by']   = isset($data['updated_by']) ? $data['updated_by'] : '';

        return $opening_balance_data;
    }

    /**
     * Get opening balance names
     *
     * @return array
     */
    public function getOpeningBalanceNames()
    {


        $rows = DB::select("SELECT id, name, start_date, end_date FROM account_financial_year");

        return $rows;
    }

    /**
     * Get opening balance date ranges
     *
     * @param int $year_id Year Id
     *
     * @return array
     */
    public function getStartEndDate($year_id)
    {
        $dates = [];


        $rows = DB::select("SELECT start_date, end_date FROM account_financial_year WHERE id = %d", [$year_id]);
        $rows = (!empty($rows)) ? $rows[0] : null;


        $dates['start'] = $rows['start_date'];
        $dates['end']   = $rows['end_date'];

        return $dates;
    }

    /**
     * Get virtual accts summary for opening balance
     *
     * @param int $year_id Year Id
     *
     * @return void
     */
    public function getObVirtualAcct($year_id)
    {

        $people = new People();
        $taxagencies = new TaxAgencies();

        $vir_ac['acct_receivable'] = DB::select("SELECT ledger_id as people_id, debit, credit from account_opening_balance where financial_year_id = %d and credit=0 and type='people'", [$year_id]);

        $vir_ac['acct_payable'] = DB::select("SELECT ledger_id as people_id, debit, credit from account_opening_balance where financial_year_id = %d and debit=0 and type='people'", [$year_id]);

        $vir_ac['tax_payable'] = DB::select("SELECT ledger_id as agency_id, debit, credit from account_opening_balance where financial_year_id = %d and debit=0 and type='tax_agency'", [$year_id]);

        for ($i = 0; $i < count($vir_ac['acct_payable']); $i++) {
            if (empty($vir_ac['acct_payable'][$i]['people_id'])) {
                return;
            }

            $vir_ac['acct_payable'][$i]['people']['id']   = $vir_ac['acct_payable'][$i]['people_id'];
            $vir_ac['acct_payable'][$i]['people']['name'] = $people->getPeopleNameByPeopleId($vir_ac['acct_payable'][$i]['people_id']);
        }

        for ($i = 0; $i < count($vir_ac['acct_receivable']); $i++) {
            if (empty($vir_ac['acct_receivable'][$i]['people_id'])) {
                return;
            }

            $vir_ac['acct_receivable'][$i]['people']['id']   = $vir_ac['acct_receivable'][$i]['people_id'];
            $vir_ac['acct_receivable'][$i]['people']['name'] = $people->getPeopleNameByPeopleId($vir_ac['acct_receivable'][$i]['people_id']);
        }

        for ($i = 0; $i < count($vir_ac['tax_payable']); $i++) {
            if (empty($vir_ac['tax_payable'][$i]['agency_id'])) {
                return;
            }

            $vir_ac['tax_payable'][$i]['agency']['id']   = $vir_ac['tax_payable'][$i]['agency_id'];
            $vir_ac['tax_payable'][$i]['agency']['name'] = $taxagencies->getTaxAgencyNameById($vir_ac['tax_payable'][$i]['agency_id']);
        }

        return $vir_ac;
    }

    /**
     * Get balance with opening balance of a ledger
     *
     * @param int      $ledger_id  Ledger Id
     * @param datetime $start_date Start Date
     * @param datetime $end_date   End Date
     *
     * @return mixed
     */
    public function getLedgerBalanceWithOpeningBalance($ledger_id, $start_date, $end_date)
    {
        $reports = new Reports();

        $trialbal = new TrialBalance();
        $ledger = new LedgerAccounts();
        $common = new CommonFunc();

        // get closest financial year id and start date
        $closest_fy_date = $trialbal->getClosestFnYearDate($start_date);

        // get opening balance data within that(^) financial year
        $opening_balance = (float) $reports->ledgerReportOpeningBalanceByFnYearId($closest_fy_date['id'], $ledger_id);

        // should we go further calculation, check the diff
        if ($common->hasDateDiff($start_date, $closest_fy_date['start_date'])) {
            $prev_date_of_start = date('Y-m-d', strtotime('-1 day', strtotime($start_date)));

            $sql1 =
                "SELECT SUM(debit - credit) AS balance
            FROM account_ledger_detail
            WHERE ledger_id = {$ledger_id} AND trn_date BETWEEN '{$closest_fy_date['start_date']}' AND '{$prev_date_of_start}'";


            $prev_ledger_details = DB::scalar($sql1);
            $opening_balance += (float) $prev_ledger_details;
        }

        // ledger details
        $sql2 =
            "SELECT
        SUM(debit-credit) as balance
        FROM account_ledger_detail
        WHERE ledger_id = {$ledger_id} AND trn_date BETWEEN '{$start_date}' AND '{$end_date}'"
        ;

        $res = DB::select($sql2);
        $res = (!empty($res)) ? $res[0] : null;

        $total_debit   = 0;
        $total_credit  = 0;
        $final_balance = 0;

        $final_balance = $opening_balance + $res['balance'];

        $l_data = $ledger->getLedger($ledger_id);

        if (empty($l_data)) {
            return [];
        }

        return [
            'id'           => $ledger_id,
            'name'         => $l_data->name,
            'code'         => $l_data->code,
            'obalance'     => $opening_balance,
            'balance'      => $final_balance,
            'total_debit'  => $total_debit,
            'total_credit' => $total_credit,
        ];
    }

    /**
     * Get opening balance invoice account details
     *
     * @param string $fy_start_date Year Date
     *
     * @return int
     */
    public function getOpbInvoiceAccountDetails($fy_start_date)
    {


        // mainly ( debit - credit )
        $sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance
            FROM invoice_account_detail WHERE trn_date < '{$fy_start_date}'
            GROUP BY invoice_no HAVING balance > 0 )
        AS get_amount";

        return (float) DB::scalar($sql);
    }

    /**
     * Get opening balance bill & purchase
     *
     * @param string $fy_start_date Start Date
     *
     * @return int
     */
    public function getOpbBillPurchaseAccountDetails($fy_start_date)
    {


        /**
         *? Why only bills, not expense?
         *? Expense is `direct expense`, and we don't include direct expense here
         */
        $bill_sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM bill_account_detail WHERE trn_date < '%s'
        GROUP BY bill_no HAVING balance < 0 )
        AS get_amount";

        $purchase_sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM purchase_account_details WHERE trn_date < '%s'
        GROUP BY purchase_no HAVING balance < 0 )
        AS get_amount";

        $bill_amount     = DB::scalar($bill_sql, [$fy_start_date]);
        $purchase_amount = DB::scalar($purchase_sql, [$fy_start_date]);

        return abs((float) $bill_amount + (float) $purchase_amount);
    }

    /**
     * Get lower and upper bound of financial years
     *
     * @return array
     */
    public function getDateBoundary()
    {


        $result = DB::select("SELECT MIN(start_date) as lower, MAX(end_date) as upper FROM account_financial_year");
        $result = (!empty($result)) ? $result[0] : null;

        return $result;
    }

    /**
     * Get current financial year
     *
     * @param datetime $date Date
     *
     * @return array
     */
    public function getCurrentFinancialYear($date = '')
    {


        if (empty($date)) {
            $date = date('Y-m-d');
        }

        $result = DB::select("SELECT id,name,start_date,end_date FROM account_financial_year WHERE '%s' between start_date AND end_date", [$date]);

        $result = (!empty($result)) ? $result[0] : null;

        return $result;
    }
}
