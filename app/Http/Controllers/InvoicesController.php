<?php

namespace App\Http\Controllers;

use App\Facades\ReusableFacades;
use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\InvoiceCreateRequest;
use App\Http\Requests\InvoiceUpdateRequest;
use App\Models\InvoiceItem;
use App\Repositories\Contracts\InvoiceRepository;
use App\Validators\InvoiceValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class InvoicesController.
 *
 * @package namespace App\Http\Controllers;
 */
class InvoicesController extends Controller
{
    /**
     * @var InvoiceRepository
     */
    protected $repository;

    /**
     * @var InvoiceValidator
     */
    protected $validator;

    /**
     * InvoicesController constructor.
     *
     * @param InvoiceRepository $repository
     * @param InvoiceValidator $validator
     */
    public function __construct(InvoiceRepository $repository, InvoiceValidator $validator)
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
        $invoices = $this->repository->with(['project','items','payments']);
        if(request()->has('project_id')){
            $invoices->where('project_id',request()->project_id);
        }
        $invoices = Auth::user()->hasRole('Customer') ? $invoices->user()->get()->append('remaining') : $invoices->get()->append('remaining') ;


        if (request()->wantsJson()) {

            return ReusableFacades::createResponse(true,$invoices);

        }

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  InvoiceCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(InvoiceCreateRequest $request)
    {
        try {
            DB::beginTransaction();

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $invoice = $this->repository->create($request->all());

            foreach ($request->items as $key => $value) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item' => $value['item'],
                    'quantity' => $value['quantity'],
                    'price' => $value['price'],
                ]);
            }

            $response = [
                'message' => 'Invoice created.',
                'data'    => $invoice->toArray(),
            ];
            DB::commit();


            if ($request->wantsJson()) {

                return ReusableFacades::createResponse(true,$invoice->toArray(),'Invoice created.');

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
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = $this->repository->with(['project','items','payments'])->find($id)->append('remaining');

        if (request()->wantsJson()) {

            return ReusableFacades::createResponse(true,$invoice,'Invoice found.');

        }

        return view('invoices.show', compact('invoice'));
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
        $invoice = $this->repository->find($id);

        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  InvoiceUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(InvoiceUpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $invoice = $this->repository->update($request->all(), $id);

            InvoiceItem::where('invoice_id',$id)->delete();

            foreach ($request->items as $key => $value) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item' => $value['item'],
                    'quantity' => $value['quantity'],
                    'price' => $value['price'],
                ]);
            }

            $response = [
                'message' => 'Invoice updated.',
                'data'    => $invoice->toArray(),
            ];
            DB::commit();

            if ($request->wantsJson()) {

                return ReusableFacades::createResponse(true, $invoice->toArray(),'Invoice updated.');
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

            return ReusableFacades::createResponse($deleted,[],'Invoice deleted.');
        }

        return redirect()->back()->with('message', 'Invoice deleted.');
    }
}
