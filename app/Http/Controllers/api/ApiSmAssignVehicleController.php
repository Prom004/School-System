<?php

namespace App\Http\Controllers\api;
use App\SmRoute;
use App\SmVehicle;
use App\ApiBaseMethod;
use App\SmAssignVehicle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\SmAcademicYear;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;

class ApiSmAssignVehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $routes = SmRoute::where('active_status', 1)->get(['id','title']);
            $vehicles = SmVehicle::select('id', 'vehicle_no')->where('active_status', 1)->get();
            $assign_vehicles = SmAssignVehicle::join('sm_routes','sm_routes.id','=','sm_assign_vehicles.route_id')
            ->join('sm_vehicles','sm_vehicles.id','=','sm_assign_vehicles.vehicle_id')->where('sm_assign_vehicles.active_status', 1)
            ->select('sm_assign_vehicles.id','sm_routes.title','sm_vehicles.vehicle_no')->get();
           
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['routes'] = $routes->toArray();
                $data['assign_vehicles'] = $assign_vehicles->toArray();
                $data['vehicles'] = $vehicles->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            //return $vehicles;
            return view('backEnd.transport.assign_vehicle', compact('routes', 'assign_vehicles', 'vehicles'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make(
            $input,
            [
                'route' => 'required|unique:sm_assign_vehicles,route_id',
                'vehicles' => 'required|array',
            ],
            [
                'vehicles.required' => 'At least one checkbox required!'
            ]
        );

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user_id = auth()->user()->id;
            
            $assign_vehicle = new SmAssignVehicle();
            $assign_vehicle->route_id = $request->route;
            $assign_vehicle->academic_id =  SmAcademicYear::API_ACADEMIC_YEAR(auth()->user()->school_id);
            $assign_vehicle->school_id = auth()->user()->school_id;
            $assign_vehicle->created_by = $user_id;
            $assign_vehicle->updated_by = $user_id;
   
            $vehicles = '';
            $i = 0;
            foreach ($request->vehicles as $vehicle) {
                $i++;
                if ($i == 1) {
                    $vehicles .=  $vehicle;
                } else {
                    $vehicles .=  ',';
                    $vehicles .=  $vehicle;
                }
            }
            $assign_vehicle->vehicle_id = $vehicles;
            $result = $assign_vehicle->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Assign Vehicle has been created successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

  
    public function edit(Request $request, $id)
    {


        try {
            $routes = SmRoute::where('active_status', 1)->get();
            $assign_vehicles = SmAssignVehicle::where('active_status', 1)->get();
            $assign_vehicle = SmAssignVehicle::find($id);
            $vehiclesIds = explode(',', $assign_vehicle->vehicle_id);
            $vehicles = SmVehicle::select('id', 'vehicle_no')->where('active_status', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['routes'] = $routes->toArray();
                $data['assign_vehicles'] = $assign_vehicles->toArray();
                $data['assign_vehicle'] = $assign_vehicle;
                $data['vehiclesIds'] = $vehiclesIds;
                $data['vehicles'] = $vehicles->toArray();
                $data['assign_vehicles'] = $assign_vehicles->toArray();
                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.transport.assign_vehicle', compact('routes', 'assign_vehicles', 'assign_vehicle', 'vehicles', 'vehiclesIds'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'route' => 'required|unique:sm_assign_vehicles,route_id,' . $id,
        ]);


        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }




        try {
            $assign_vehicle = SmAssignVehicle::find($id);
            $assign_vehicle->route_id = $request->route;
            $vehicles = '';
            $i = 0;
            foreach ($request->vehicles as $vehicle) {
                $i++;
                if ($i == 1) {
                    $vehicles .=  $vehicle;
                } else {
                    $vehicles .=  ',';
                    $vehicles .=  $vehicle;
                }
            }
            $assign_vehicle->vehicle_id = $vehicles;
            $result = $assign_vehicle->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Assign vehicle has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('assign-vehicle');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
 
    public function delete(Request $request)
    {

        try {
            $result = SmAssignVehicle::where('id', $request->id)->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Assign vehicle has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('assign-vehicle');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
}
