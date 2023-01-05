<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use DB;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class UserController extends Controller
{
    public function index()
    {
        // echo "yes Users Controller";
        // exit;
        $userlist = User::all();
        return view('pages.users.index', compact('userlist'));
    }

    public function create()
    {
        return view('pages.users.create');
    }

    public function show($id)
    {
        
    }

    public function edit($id)
    {
        $data['posts'] = User::find($id);
        return View::make('pages.users.edit', $data);
    }

    public function setpasswd(Request $request)
    {

        $id6 = $request->userid;
        $tok = $request->mytoken;
        $data = DB::table('users')
                ->where('id','=',$id6)->where('token','=',$tok)
                ->update(['password' => Hash::make($request->password1), 'updated_at'=>date("Y-m-d H:i:s")]);


        ///// Sending Email to Admin

             $AdminMsg = 'New Registration from Investors Connect  <br/> <br/>Email ID : <b>' . $request->emailid . "</b>  -  Approve or Deny using the link : <a target='_blank' href='". env('APP_URL') ."/approvedeny.php?uid=".$id6."'>Approve or Deny</a> <br/><br/><br/>  Thanks and Regards <br/> BrandIdea Admin Team";

             $mail2 = new PHPMailer(true);

            try {

                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                // $mail->isSMTP();                                            // Send using SMTP
                // $mail->Host       = 'smtp.mail.eu-west-1.awsapps.com';      // Set the SMTP server to send through
                // $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                // $mail->Username   = 'BrandIdea-app';                          // SMTP username
                // $mail->Password   = 'Chamundi@299';                         // SMTP password
                // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                // $mail->Port       = 465;                                    

                $mail2->isMail();
                $mail2->setFrom('shobamohandurai@gmail.com', 'Admin-BrandIdea');
                $mail2->addAddress('mohan.durai@BrandIdea.com', 'Investors Connect');
                //$mail2->addCC('', 'New Registration from Inverstors Connect Signup');
                $mail2->Subject = 'New Registration from Inverstors Connect';
                $mail2->msgHTML($AdminMsg);
                // optional - msgHTML will create an alternate automatically
                // $mail->AltBody = 'Welcome Admin ! - New Signup from Investors Connect Info <br/> Name : ' . $request->firstname . " " . $request->lastname . "<br/><br/>" . " Approve or Deny using the link : <a target='_blank' href='http://localhost/investor-connect/approvedeny.php'>Approve or Deny</a> <br/><br/><br/>  Thanks and Regards Simrema Team";
                //$mail->addAttachment('images/phpmailer_mini.png'); // attachment
                $mail2->action_function = 'callbackAction';
                //$mail->send();
            } catch (Exception $e) {
                echo $e->errorMessage();
            }

            //Alternative approach using a closure
            try {
                $mail2->action_function = static function ($result, $to, $cc, $bcc, $subject, $body) {
                    if ($result) {
                        echo "Message sent successfully\n";
                    } else {
                        echo "Message send failed\n";
                    }
                };
                $mail2->send();
            } catch (Exception $e) {
                echo $e->errorMessage();
            }


       ///    Ends Email sending procoess


        return Redirect::to('/auth/login')->with('message',"Password Updated Successfully !!! Please login after approval from Admin Team from BrandIdea., You will be notified in your registered email id for login confirmattion !!! Thank You");
    }

    public function register(Request $request)
    {
        // print_r($request->all());    
        // exit;
        $validations = [
            'username' => ['required'],
            'phone' => ['required']
        ];
        $validator = Validator::make($request->all(), $validations, []);
        if ($validator->fails()) {
            $this->message = $validator->errors();
        } else {
            $user = new User();

            $str6 = "BrandIdea@123" . date("Y-m-d H:i:s");
            $mytoken = md5($str6);
            $user->token = $mytoken;
            $user->firstname = "";
            $user->lastname = "";
            $user->designation = "";
            $user->phone = $request->phone;
            $user->country = "";
            $user->Organization = "";
            $user->about_orgn = "";
            $user->email = $request->username;
            $user->user_type = "INVESTOR";
            $user->status = "InActive";
            $user->password = "";
            //$user->password = Hash::make($request->password1);

            $user->created_by = 1;
            $user->updated_by = 1;

            $this->status = true;
            $this->modal = true;
 
            $user->save();
           
            //// Start Email sending module

            function callbackAction($result, $to, $cc, $bcc, $subject, $body)
            {
                echo "Message subject: \"$subject\"\n";
                foreach ($to as $address) {
                    echo "Message to {$address[1]} <{$address[0]}>\n";
                }
                foreach ($cc as $address) {
                    echo "Message CC to {$address[1]} <{$address[0]}>\n";
                }
                foreach ($bcc as $toaddress) {
                    echo "Message BCC to {$toaddress[1]} <{$toaddress[0]}>\n";
                }
                if ($result) {
                    echo "Message sent successfully\n";
                    exit;

                } else {
                    echo "Message send failed\n";
                    exit;
                }
            }

            $InvMsg = "Please click the following link to verify your email id <br/><br/> <a href='". env('APP_URL') ."/auth/setpasswd?mytoken=" . $mytoken . "&userid=" . $user->id . "&emailid=" . $request->username . "'> Verify Email </a> <br/><br/> Regards - Simrema Team";

            require_once 'vendor/autoload.php';

            $mail = new PHPMailer(true);

            try {

                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                // $mail->isSMTP();                                            // Send using SMTP
                // $mail->Host       = 'smtp.mail.eu-west-1.awsapps.com';      // Set the SMTP server to send through
                // $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                // $mail->Username   = 'BrandIdea-app';                          // SMTP username
                // $mail->Password   = 'Chamundi@299';                         // SMTP password
                // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                // $mail->Port       = 465;                                    

                $mail->isMail();
                //$mail->setFrom('BrandIdea-app@BrandIdea.com', 'Admin-BrandIdea');
                $mail->setFrom('shobamohandurai@gmail.com', 'Admin-BrandIdea');
                $mail->addAddress($request->username, 'Investors Connect Admin');
               
                $mail->Subject = 'New Registration from Inverstors Connect';
                $mail->msgHTML($InvMsg);
                // optional - msgHTML will create an alternate automatically
                // $mail->AltBody = 'Hello Welcome Dear ' . $request->firstname . " " . $request->lastname . " - Please wait for login authentication after apprval from our Admin Team !!!.   The Approval Confirmation will be notified in your Email ID Shortly !!!.   Thanks and Regards Simrema Team";
                //$mail->addAttachment('images/phpmailer_mini.png'); // attachment
                $mail->action_function = 'callbackAction';
                //$mail->send();
            } catch (Exception $e) {
                echo $e->errorMessage();
            }

            //Alternative approach using a closure
            try {
                $mail->action_function = static function ($result, $to, $cc, $bcc, $subject, $body) {
                    if ($result) {
                        echo "Message sent successfully\n";
                    } else {
                        echo "Message send failed\n";
                    }
                };
                $mail->send();
            } catch (Exception $e) {
                echo $e->errorMessage();
            }

        }

        return Redirect::to('/auth/login')->with('message',"User Registered Successfully !!! Email verification sent on your registered Email ID, click on the url and set Password. BrandIdea Admin Team !!!");
    }


    public function update(Request $request, $id)
    {
        $request->validate([]);
        $user = User::find($id);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->designation = $request->designation;
        $user->phone = $request->phone;
        $user->country = $request->country;
        $user->Organization = $request->Organization;
        $user->about_orgn = $request->about_orgn;
        $user->email = $request->username;
        $user->user_type = $request->user_type;
        $user->status = $request->status;
        $this->status   = true;
        $this->modal = true;
        $user->save();


        // function callbackAction($result, $to, $cc, $bcc, $subject, $body)
        // {
        //     echo "Message subject: \"$subject\"\n";
        //     foreach ($to as $address) {
        //         echo "Message to {$address[1]} <{$address[0]}>\n";
        //     }
        //     foreach ($cc as $address) {
        //         echo "Message CC to {$address[1]} <{$address[0]}>\n";
        //     }
        //     foreach ($bcc as $toaddress) {
        //         echo "Message BCC to {$toaddress[1]} <{$toaddress[0]}>\n";
        //     }
        //     if ($result) {
        //         echo "Message sent successfully\n";
        //     } else {
        //         echo "Message send failed\n";
        //     }
        // }

        // require_once 'vendor/autoload.php';

        // $mail = new PHPMailer(true);

        // try {

        //     //Server settings
        //     $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        //     $mail->isSMTP();                                            // Send using SMTP
        //     $mail->Host       = 'smtp.mail.eu-west-1.awsapps.com';                    // Set the SMTP server to send through
        //     $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        //     $mail->Username   = 'BrandIdea-app';                     // SMTP username
        //     $mail->Password   = 'Chamundi@299';                               // SMTP password
        //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        //     $mail->Port       = 465;                                    

        //     $mail->isMail();
        //     $mail->setFrom('BrandIdea-app@BrandIdea.com', 'Admin-BrandIdea');
        //     $mail->addAddress('mohan_durai@yahoo.com', 'Test Mail from Invester Connect');
        //     $mail->addCC('mohandurai@gmail.com', 'New Registration from Inverstors Connect');
        //     $mail->addCC('mohan.durai@BrandIdea.com', 'New Registration from Inverstors Connect');
        //     $mail->Subject = 'New Registration from Inverstors Connect';
        //     $mail->msgHTML('This is the HTML message body <b>in bold!</b>');
        //     // optional - msgHTML will create an alternate automatically
        //     $mail->AltBody = 'AAAAAAAA This is the body in plain text for non-HTML mail clients';
        //     //$mail->addAttachment('images/phpmailer_mini.png'); // attachment
        //     $mail->action_function = 'callbackAction';
        //     //$mail->send();
        // } catch (Exception $e) {
        //     echo $e->errorMessage();
        // }

        // //Alternative approach using a closure
        // try {
        //     $mail->action_function = static function ($result, $to, $cc, $bcc, $subject, $body) {
        //         if ($result) {
        //             echo "Message sent successfully\n";
        //         } else {
        //             echo "Message send failed\n";
        //         }
        //     };
        //     $mail->send();
        // } catch (Exception $e) {
        //     echo $e->errorMessage();
        // }

        return Redirect::to('/user')->with('success', 'User updated successfully');
    }

    public function changepwd($id)
    {
        return view('pages.users.changepwd')->with("id",$id);
    }

    public function updatepwd(Request $request)
    {
       
        $userid = User::find($request->id);

        // echo $userid->email . " <<<========" . $request->password;
        // exit;

        $userdata = array (
            'email' => $userid->email,
            'password' => $request->password
        );

        If (Auth::attempt($userdata)) {
            // echo "User Athentaition is true";
            // exit;
            $userid->password = Hash::make($request->password1);
            $userid->save();
            return Redirect::to('/dashboard')->with('success', 'New Password updated successfully');
        } else {
            // echo "User Athentaition is wrong !!!!!!!!";
            // exit;
            return Redirect::to('/dashboard')->withErrors('Incorrect login details');
        }

    }

    public function store(Request $request)
    {
        
        $validations = [
            'name' => ['required'],
            'email' => ['required'],
            'type' => ['required']
        ];
        $validator = Validator::make($request->all(), $validations, []);
        if ($validator->fails()) {
            $this->message = $validator->errors();
        } else {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->type = $request->type;
            $user->password = Hash::make("Pass@123");

            $user->created_by = 1;
            $user->updated_by = 1;
            $user->save();
            $this->status = true;
            $this->modal = true;
            $this->message = "New User Added Successfully!";
        }

        return redirect('/user')->with('success', 'New User Created Successfully');
    }

    public function destroy($id)
    {
        $lead = User::findOrFail($id);
        $lead->delete();
        return redirect('/user')->with('success', 'Record Successfully Deleted');
    }


    public function resetpwd(Request $request)
    {

        $userid = User::find($request->id);

        $userid->password = Hash::make("Pass@123");
        $userid->update();
        return Redirect::to('/dashboard')->with('success', 'Password Reset successfully');

    }

    


}
