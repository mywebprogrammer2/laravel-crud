<?php

namespace App\Http\Controllers;

use App\Facades\ReusableFacades;
use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\PaymentCreateRequest;
use App\Http\Requests\PaymentUpdateRequest;
use App\Repositories\Contracts\PaymentRepository;
use App\Validators\PaymentValidator;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentsController.
 *
 * @package namespace App\Http\Controllers;
 */
class PaymentsController extends Controller
{
    /**
     * @var PaymentRepository
     */
    protected $repository;

    /**
     * @var PaymentValidator
     */
    protected $validator;

    /**
     * PaymentsController constructor.
     *
     * @param PaymentRepository $repository
     * @param PaymentValidator $validator
     */
    public function __construct(PaymentRepository $repository, PaymentValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        $payments = $this->repository->with(['invoice']);
        if(request()->has('invoice_id')){
            $payments = $payments->where('invoice_id',request()->invoice_id);
        }
        $payments = $payments->get();

        if (request()->wantsJson()) {

            return ReusableFacades::createResponse(true,$payments);

        }

        return view('payments.index', compact('payments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PaymentCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(PaymentCreateRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $payment = $this->repository->create($request->all());

            $response = [
                'message' => 'Payment created.',
                'data'    => $payment->toArray(),
            ];
            DB::commit();

            if ($request->wantsJson()) {
                return ReusableFacades::createResponse(true,$payment,'Payment created.');

            }

            return redirect()->back()->with('message', $response['message']);
        } catch (ValidatorException $e) {
            DB::rollback();

            if ($request->wantsJson()) {
                return ReusableFacades::createResponse(false,[],'',$e->getMessageBag(),400);

            }

            return redirect()->back()->withErrors($e->getMessageBag())->withInput();
        }
        catch (\Exception $e){

            DB::rollBack();
            return ReusableFacades::createResponse(false,[],$e->getMessage(),[],400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payment = $this->repository->with(['invoice'])->find($id);

        if (request()->wantsJson()) {

            return ReusableFacades::createResponse(true,$payment,'Payment found.');

        }

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $payment = $this->repository->find($id);

        return view('payments.edit', compact('payment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PaymentUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(PaymentUpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $payment = $this->repository->update($request->all(), $id);

            $response = [
                'message' => 'Payment updated.',
                'data'    => $payment->toArray(),
            ];
            DB::commit();

            if ($request->wantsJson()) {

                return ReusableFacades::createResponse(true, $payment->toArray(),'Payment updated.');

            }

            return redirect()->back()->with('message', $response['message']);
        } catch (ValidatorException $e) {

            DB::rollBack();
            if ($request->wantsJson()) {

                return ReusableFacades::createResponse(false,[],'',$e->getMessageBag(),400);

            }

            return redirect()->back()->withErrors($e->getMessageBag())->withInput();
        }
        catch (\Exception $e){

            DB::rollBack();

            return ReusableFacades::createResponse(false,[],$e->getMessage(),[],400);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = $this->repository->delete($id);

        if (request()->wantsJson()) {

            return ReusableFacades::createResponse($deleted,[],'Payment deleted.');

        }

        return redirect()->back()->with('message', 'Payment deleted.');
    }
}
