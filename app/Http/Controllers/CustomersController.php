<?php

namespace App\Http\Controllers;

use App\Facades\ReusableFacades;
use Illuminate\Http\Request;

use App\Http\Requests;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Http\Requests\CustomerCreateRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Models\UserDetail;
use App\Repositories\Contracts\CustomerRepository;
use App\Validators\CustomerValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Class CustomersController.
 *
 * @package namespace App\Http\Controllers;
 */
class CustomersController extends Controller
{
    /**
     * @var CustomerRepository
     */
    protected $repository;

    /**
     * @var CustomerValidator
     */
    protected $validator;

    /**
     * CustomersController constructor.
     *
     * @param CustomerRepository $repository
     * @param CustomerValidator $validator
     */
    public function __construct(CustomerRepository $repository, CustomerValidator $validator)
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
        $customers = $this->repository
        ->with('user_detail')
        ->findWhere([
            ['roles','HAS',function($q){
                $q->where( 'name', 'Customer');
            }]
        ])
        ->all();

        if (request()->wantsJson()) {

            return ReusableFacades::createResponse(true,$customers);

        }

        return view('customers.index', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CustomerCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(CustomerCreateRequest $request)
    {
        try {
            DB::beginTransaction();

            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_CREATE);

            $data = $request->all();
            $data['password'] = Hash::make(Str::random(8));

            $customer = $this->repository->create($data);

            $customer->assignRole('Customer');

            if($request->has('phone') && $request->phone != '' || $request->has('address') && $request->address != '' ){
                $data['users_id'] = $customer->id;
                UserDetail::create($data);
            }

            $response = [
                'message' => 'Customer created.',
                'data'    => $customer->toArray(),
            ];

            DB::commit();


            if ($request->wantsJson()) {

                return ReusableFacades::createResponse(true,$customer->toArray(),'Customer created.');

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
        $customer = $this->repository->with('user_detail')->find($id);

        if (request()->wantsJson()) {

            return ReusableFacades::createResponse(true,$customer,'Customer found.');

        }

        return view('customers.show', compact('customer'));
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
        $customer = $this->repository->find($id);

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CustomerUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(CustomerUpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $this->validator->with($request->all())->setId($id)->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $customer = $this->repository->update($request->all(), $id);


            if($request->has('phone') && $request->phone != '' || $request->has('address') && $request->address != '' ){
                $data =$request->all();
                UserDetail::updateOrCreate(['users_id'=>$id],$data);
            }

            DB::commit();


            $response = [
                'message' => 'Customer updated.',
                'data'    => $customer->toArray(),
            ];

            if ($request->wantsJson()) {

                return ReusableFacades::createResponse(true, $customer->toArray(),'Customer updated.');


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

            return ReusableFacades::createResponse($deleted,[],'Customer deleted.');

        }

        return redirect()->back()->with('message', 'Customer deleted.');
    }
}
