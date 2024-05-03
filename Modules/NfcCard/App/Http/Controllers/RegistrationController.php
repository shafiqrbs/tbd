<?php

namespace Modules\NfcCard\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Appfiy\Entities\Component;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Nfccard\App\Models\NfcUserModel;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $components = NfcUserModel::orderBy('id','DESC')->paginate(10);
        return view('nfccard::registration/index',['entities'=>$components]);
    }


    // Create Form
    public function createUserForm(Request $request) {
        return view('nfccard::registration/create');
    }


    // Store Form data in database
    public function UserForm(Request $request) {
        // Form validation
        $this->validate($request, [
            'name' => 'required',
            'designation' => 'required',
            'email' => 'required|email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'website'=>'required',
            'address' => 'required'
        ]);

        $input = $request->all();

//        dd($request->all());

        if ($request->file('profile_pic') != '') {
            $target_location = 'uploads/nfc-card/';
//            File::delete(public_path().'/'.$target_location.$component->image);
            $avatar = $request->file('profile_pic');
            $file_title = bin2hex(random_bytes(15)).'.'.$avatar->getClientOriginalExtension();
            $input['profile_pic'] = $file_title;
            if (!Storage::disk('public')->exists($target_location)) {
                $target_location = public_path($target_location);
                File::makeDirectory($target_location, 0777, true, true);
            }
            $path = $target_location;
            $target_file =  $path.basename($file_title);
            $file_path = $_FILES['profile_pic']['tmp_name'];
            move_uploaded_file($file_path,$target_file);
        }else{
            $input['profile_pic'] = null;
        }

        if ($request->file('company_logo') != '') {
            $target_location = 'uploads/nfc-card/';
//          File::delete(public_path().'/'.$target_location.$component->image);
            $avatar = $request->file('company_logo');
            $file_title = bin2hex(random_bytes(15)).'.'.$avatar->getClientOriginalExtension();
            $input['company_logo'] = $file_title;
            if (!Storage::disk('public')->exists($target_location)) {
                $target_location = public_path($target_location);
                File::makeDirectory($target_location, 0777, true, true);
            }
            $path = $target_location;
            $target_file =  $path.basename($file_title);
            $file_path = $_FILES['company_logo']['tmp_name'];
            move_uploaded_file($file_path,$target_file);
        }else{
            $input['company_logo'] = null;
        }

        //  Store data in database
        NfcUserModel::create($input);
        return back()->with('success', 'Your form has been submitted.');
    }


}
