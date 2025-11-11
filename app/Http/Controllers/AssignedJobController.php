<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Order;
use App\Models\OrderedProduct;



class AssignedJobController extends Controller
{


    /**
     * Edit a order.
     *
     * @param int $id The ID of the order to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {
        $orderedProduct = OrderedProduct::find($id);
        $order = Order::find($orderedProduct->order_id);

        return view('assigned-jobs.edit', compact('order', 'orderedProduct'));
    }

    public function list(): View
    {
        return view('assigned-jobs.list');
    }

    /**
     * Update the model
     * @return JsonResponse
     * */
    public function update(Request $request): JsonResponse
    {
        $timestamp = now();

        $orderedProductId = $request->input('ordered_product_id');

        $validatedData = [];
        $validatedData['staff_status'] = $request['staff_status'];
        $validatedData['staff_status_note'] = $request['staff_status_note'];
        $orderModel = OrderedProduct::whereId($orderedProductId)->update($validatedData);

        if ($orderModel) {
            return response()->json([
                'status'    => false,
                'message' => __('app.record_saved_successfully'),
                'id' => $orderedProductId,
            ]);
        } else {
            return response()->json([
                'message' => __('app.failed_to_save_record'),
            ], 409);
        }
    }

    public function datatableList(Request $request)
    {

        $data = OrderedProduct::with('order')
            ->when(Auth::user() && !optional(Auth::user())->hasRole('SuperAdmin'), function ($query) {
                //Removed Super Admin from hasRole Condition
                return $query->whereNotNull('assigned_user_id')->where('assigned_user_id', Auth::user()->id);
            });

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('order_code', function ($row) {
                return $row->order->order_code;
            })
            ->addColumn('start_date', function ($row) {
                return $row->formatted_start_date;
            })
            ->addColumn('end_date', function ($row) {
                return $row->formatted_end_date;
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format(app('company')['date_format']) : null;
            })
            ->addColumn('customer_name', function ($row) {
                return $row->order->party->first_name;
            })
            ->addColumn('mobile', function ($row) {
                return $row->order->party->mobile;
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;

                $editUrl = route('assigned_jobs.edit', ['id' => $id]);


                $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"></i><i class="bx bx-alarm-add"></i> ' . __('app.update_status') . '</a>
                                </li>
                            </ul>
                        </div>';
                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
