<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Storage;
use Log;
use Modules\Superadmin\Entities\Subscription;
use Modules\Superadmin\Entities\Package;
use Carbon\Carbon;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Sarfraznawaz2005\BackupManager\Facades\BackupManager;
class BackUpController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $subscription = Subscription::active_subscription($business_id);
        $cron_job_command = $this->commonUtil->getCronJobCommand();
        $backups = BackupManager::getBackups();
        return view("backup.index")
            ->with(compact('backups', 'cron_job_command'));
    }

    /**
     * Create a resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         set_time_limit(-1);
    //     $dbhost = env('DB_HOST');
    //     $dbuser = env('DB_USERNAME');
    //     $dbpass = env('DB_PASSWORD');
    //     $dbname = env('DB_DATABASE');
    //     $mysqldump=exec('which mysqldump');
        
        
    //     $command = "$mysqldump --opt -h $dbhost -u $dbuser -p $dbpass $dbname > $dbname.sql";
        
    //   return  exec($command);
        if (!auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        //Disable in demo
        $notAllowed = $this->commonUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }
        $business_id = request()->session()->get('user.business_id');
        $subscription = Subscription::active_subscription($business_id);
        
        if($subscription){}
        $result = BackupManager::createBackup();
        $message = 'Files Backup Failed';
        $messages[] = [
                    'success' => 0,
                    'msg' => $message
                ];
        if ($result['f'] === true) {
            $message = 'Files Backup Taken Successfully';
            $messages[] = ['success' => 1,
                'msg' => __('lang_v1.success')
            ];
        } 
        return back()->with('status', $output);
    }

    /**
     * Downloads a backup zip file.
     *
     * TODO: make it work no matter the flysystem driver (S3 Bucket, etc).
     */
    public function download($file_name)
    {
        if (!auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        $path = config('backupmanager.backups.backup_path') . DIRECTORY_SEPARATOR . $file;

        $file = Storage::disk(config('backupmanager.backups.disk'))
                ->getDriver()
                ->getAdapter()
                ->getPathPrefix() . $path;

        return response()->download($file);
    }

    /**
     * Deletes a backup file.
     */
    public function delete($file_name)
    {
        if (!auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        //Disable in demo
        if (config('app.env') == 'demo') {
            $output = ['success' => 0,
                            'msg' => 'Feature disabled in demo!!'
                        ];
            return back()->with('status', $output);
        }

        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        if ($disk->exists(config('backup.backup.name') . '/' . $file_name)) {
            $disk->delete(config('backup.backup.name') . '/' . $file_name);
            return redirect()->back();
        } else {
            abort(404, "The backup file doesn't exist.");
        }
    }
    function store(Request $request)
    {
    
         try {
            set_time_limit(-1); 
          
            $photoName ='up_'.$request->backup->getClientOriginalName();
    
            $file= $request->backup->move(public_path('uploads/').config('backup.backup.name'), $photoName);
            
             $results = BackupManager::restoreBackups([$photoName]);
             if ($results[0]['d'] === true) {
                    $output = ['success' => 1,
                        'msg' => __('lang_v1.success')
                ];
             }
             else
             {
                  $output = ['success' => 0,
                            'msg' => 'Not Uploaded'
                        ];
             }
            // $sql_dump = File::get($file);
            // DB::connection()->getPdo()->exec($sql_dump);
            
            $results = BackupManager::deleteBackups([$photoName]);
          
        
         } 
   
        catch (Exception $e) {
                $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
        }
        
       
      return back()->with('status', $output);
    }
}
