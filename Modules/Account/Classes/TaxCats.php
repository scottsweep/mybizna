<?php

namespace Modules\Account\Classes;

use Illuminate\Support\Facades\DB;

class TaxCats
{

    /**
     * Get all tax categories
     *
     * @return mixed
     */
    function getAllTaxCats($args = [])
    {


        $defaults = [
            'number'  => 20,
            'offset'  => 0,
            'orderby' => 'id',
            'order'   => 'DESC',
            'count'   => false,
            's'       => '',
        ];

        $args = wp_parse_args($args, $defaults);

        $tax_cats_count  = $tax_cats     = false;

        if (false === $tax_cats) {
            $limit = '';

            if (-1 !== $args['number']) {
                $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
            }

            $sql  = 'SELECT';
            $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
            $sql .= "FROM {$wpdb->prefix}erp_acct_tax_categories ORDER BY {$args['orderby']} {$args['order']} {$limit}";

            if ($args['count']) {
                $tax_cats_count = $wpdb->get_var($sql);

                wp_cache_set($cache_key_count, $tax_cats_count, 'erp-accounting');
            } else {
                $tax_cats = $wpdb->get_results($sql, ARRAY_A);

                wp_cache_set($cache_key, $tax_cats, 'erp-accounting');
            }
        }

        if ($args['count']) {
            return $tax_cats_count;
        }

        return $tax_cats;
    }

    /**
     * Get an single tax category
     *
     * @param $tax_no
     *
     * @return mixed
     */
    function getTaxCat($tax_no)
    {


        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}erp_acct_tax_categories WHERE id = %d LIMIT 1", $tax_no), ARRAY_A);

        return $row;
    }

    /**
     * Get an single tax category
     *
     * @param $tax_no
     *
     * @return mixed
     */
    function getTaxCategoryById($id)
    {


        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}erp_acct_tax_categories WHERE id = %d LIMIT 1", $id), ARRAY_A);

        return $row;
    }

    /**
     * Insert tax category data
     *
     * @param $data
     *
     * @return int
     */
    function insertTaxCat($data)
    {


        $created_by         = auth()->user()->id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $created_by;

        $tax_data = $taxes->getFormattedTaxData($data);

        DB::table('erp_acct_tax_categories')
            ->insert(
                [
                    'name'        => $tax_data['name'],
                    'description' => $tax_data['description'],
                    'created_at'  => $tax_data['created_at'],
                    'created_by'  => $tax_data['created_by'],
                    'updated_at'  => $tax_data['updated_at'],
                    'updated_by'  => $tax_data['updated_by'],
                ]
            );

        $tax_id = $wpdb->insert_id;


        return $tax_id;
    }

    /**
     * Update tax category
     *
     * @param $data
     *
     * @return int
     */
    function updateTaxCat($data, $id)
    {


        $updated_by         = auth()->user()->id;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = $updated_by;

        $tax_data = $taxes->getFormattedTaxData($data);

        $wpdb->update(
            'erp_acct_tax_categories',
            [
                'name'        => $tax_data['name'],
                'description' => $tax_data['description'],
                'updated_at'  => $tax_data['updated_at'],
                'updated_by'  => $tax_data['updated_by'],
            ],
            [
                'id' => $id,
            ]
        );


        return $id;
    }

    /**
     * Delete a tax category
     *
     * @param $tax_no
     *
     * @return int
     */
    function deleteTaxCat($id)
    {


        $wpdb->delete('erp_acct_tax_categories', ['id' => $id]);


        return $id;
    }
}