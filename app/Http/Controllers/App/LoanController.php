<?php

namespace App\Http\Controllers\App;


use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\LoanCharge;
use App\Models\LoanFee;
use App\Models\LoanFeeMeta;
use App\Models\LoanGuarantor;
use App\Models\LoanOverduePenalty;
use App\Models\LoanProduct;
use App\Models\LoanProductCharge;
use App\Models\LoanRepayment;
use App\Models\LoanRepaymentMethod;
use App\Models\LoanDisbursedBy;
use App\Models\Borrower;
use App\Models\LoanSchedule;
use App\Models\LoanTransaction;
use App\Models\Setting;
use App\Models\Sms;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;

class LoanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    //use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
   // protected $redirectTo = '/';

    public function store(Request $request)
    {		
        
        $loan_product = LoanProduct::find($request->loan_product_id);
        if ($request->principal > $loan_product->maximum_principal) {
            Flash::warning(trans('general.principle_greater_than_maximum') . "(" . $loan_product->maximum_principal . ")");
            return redirect()->back()->withInput();
        }
        if ($request->principal < $loan_product->minimum_principal) {
            Flash::warning(trans('general.principle_less_than_minimum') . "(" . $loan_product->minimum_principal . ")");
            return redirect()->back()->withInput();
        }
        if ($request->interest_rate > $loan_product->maximum_interest_rate) {
            Flash::warning(trans('general.interest_greater_than_maximum') . "(" . $loan_product->maximum_interest_rate . ")");
            return redirect()->back()->withInput();
        }
        if ($request->interest_rate < $loan_product->minimum_interest_rate) {
            Flash::warning(trans('general.interest_less_than_minimum') . "(" . $loan_product->minimum_interest_rate . ")");
            return redirect()->back()->withInput();
        }

        $loan = new Loan();
        $loan->principal = $request->principal;
        $loan->interest_method = $request->interest_method;
        $loan->interest_rate = $request->interest_rate;
        $loan->branch_id = session('branch_id');
        $loan->interest_period = $request->interest_period;
        $loan->loan_duration = $request->loan_duration;
        $loan->loan_duration_type = $request->loan_duration_type;
        $loan->repayment_cycle = $request->repayment_cycle;
        $loan->decimal_places = $request->decimal_places;
        $loan->override_interest = $request->override_interest;
        $loan->override_interest_amount = $request->override_interest_amount;
        $loan->grace_on_interest_charged = $request->grace_on_interest_charged;
        $loan->borrower_id = $request->borrower_id;
        $loan->applied_amount = $request->principal;
        $loan->loan_officer_id = $request->loan_officer_id;
        $loan->user_id = Sentinel::getUser()->id;
        $loan->loan_product_id = $request->loan_product_id;
        $loan->release_date = $request->release_date;
        $date = explode('-', $request->release_date);
        $loan->month = $date[1];
        $loan->year = $date[0];
        if (!empty($request->first_payment_date)) {
            $loan->first_payment_date = $request->first_payment_date;
        }
        $loan->description = $request->description;
        $files = array();
        if (!empty($request->file('files'))) {
            $count = 0;
            foreach ($request->file('files') as $key) {
                $file = array('files' => $key);
                $rules = array('files' => 'required|mimes:jpeg,jpg,bmp,png,pdf,docx,xlsx');
                $validator = Validator::make($file, $rules);
                if ($validator->fails()) {
                    Flash::warning(trans('general.validation_error'));
                    return redirect()->back()->withInput()->withErrors($validator);
                } else {
                    $fname = "loan_" . uniqid() . '.' . $key->guessExtension();
                    $files[$count] = $fname;
                    $key->move(public_path() . '/uploads',
                        $fname);
                }
                $count++;
            }
        }
        $loan->files = serialize($files);
        $loan->save();

        //save custom meta
        $custom_fields = CustomField::where('category', 'loans')->get();
        foreach ($custom_fields as $key) {
            $custom_field = new CustomFieldMeta();
            $id = $key->id;
            $custom_field->name = $request->$id;
            $custom_field->parent_id = $loan->id;
            $custom_field->custom_field_id = $key->id;
            $custom_field->category = "loans";
            $custom_field->save();
        }

        if (!empty($request->charges)) {
            //loop through the array
            foreach ($request->charges as $key) {
                $amount = "charge_amount_" . $key;
                $date = "charge_date_" . $key;
                $loan_charge = new LoanCharge();
                $loan_charge->loan_id = $loan->id;
                $loan_charge->user_id = Sentinel::getUser()->id;
                $loan_charge->charge_id = $key;
                $loan_charge->amount = $request->$amount;
                if (!empty($request->$date)) {
                    $loan_charge->date = $request->$date;
                }
                $loan_charge->save();
            }
        }


        $period = GeneralHelper::loan_period($loan->id);
        $loan = Loan::find($loan->id);
        if ($loan->repayment_cycle == 'daily') {
            $repayment_cycle = 'day';
            $loan->maturity_date = date_format(date_add(date_create($request->first_payment_date),
                date_interval_create_from_date_string($period . ' days')),
                'Y-m-d');
        }
        if ($loan->repayment_cycle == 'weekly') {
            $repayment_cycle = 'week';
            $loan->maturity_date = date_format(date_add(date_create($request->first_payment_date),
                date_interval_create_from_date_string($period . ' weeks')),
                'Y-m-d');
        }
        if ($loan->repayment_cycle == 'monthly') {
            $repayment_cycle = 'month';
            $loan->maturity_date = date_format(date_add(date_create($request->first_payment_date),
                date_interval_create_from_date_string($period . ' months')),
                'Y-m-d');
        }
        if ($loan->repayment_cycle == 'bi_monthly') {
            $repayment_cycle = 'month';
            $loan->maturity_date = date_format(date_add(date_create($request->first_payment_date),
                date_interval_create_from_date_string($period . ' months')),
                'Y-m-d');
        }
        if ($loan->repayment_cycle == 'quarterly') {
            $repayment_cycle = 'month';
            $loan->maturity_date = date_format(date_add(date_create($request->first_payment_date),
                date_interval_create_from_date_string($period . ' months')),
                'Y-m-d');
        }
        if ($loan->repayment_cycle == 'semi_annually') {
            $repayment_cycle = 'month';
            $loan->maturity_date = date_format(date_add(date_create($request->first_payment_date),
                date_interval_create_from_date_string($period . ' months')),
                'Y-m-d');
        }
        if ($loan->repayment_cycle == 'yearly') {
            $repayment_cycle = 'year';
            $loan->maturity_date = date_format(date_add(date_create($request->first_payment_date),
                date_interval_create_from_date_string($period . ' years')),
                'Y-m-d');
        }
        $loan->save();
        GeneralHelper::audit_trail("Added loan with id:" . $loan->id);
        Flash::success(trans('general.successfully_saved'));
        return redirect('loan/data');
    }

	
	
    
}
