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
       // return config('backup.backup.destination.disks')[0];
      //  return config('backup.backup.name');
         $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

        $files = $disk->files(config('backup.backup.name'));
        
        //No of Days Auto Delete
        $business_id = request()->session()->get('user.business_id');
        $subscription = Subscription::active_subscription($business_id);
      //  $packageId=33;
      if($subscription){
            $packageId=$subscription->package_id;
      
            $Package=Package::find($packageId);
            if($Package)
            {
                $no_of_day= Package::find($packageId)->no_of_day;
                $date=strtotime(now()->subdays($no_of_day)); 
                foreach ($files as $k => $f) {
                      
                        if($date>$disk->lastModified($f))
                        {
                            $this->delete(str_replace(config('backup.backup.name') . '/', '', $f));
                        }
                       
                }
            }
      }
        //End No of Days Auto Delete
        $backups = [];
         // make an array of backup files, with their filesize and creation date
         foreach ($files as $k => $f) {
            // only take the zip files into account
                        if (substr($f, -3) == '.gz' && $disk->exists($f)) {
                            $backups[] = [
                                'file_path' => $f,
                                'file_name' => str_replace(config('backup.backup.name') . '/', '', $f),
                                'file_size' => $disk->size($f),
                                'last_modified' => $disk->lastModified($f),
                            ];
                        }
         }

        // reverse the backups, so the newest one would be on top
        $backups = array_reverse($backups);

        $cron_job_command = $this->commonUtil->getCronJobCommand();

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

        try {
            //Disable in demo
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }
           $result = BackupManager::createBackup();
        // $path=public_path('uploads').'/'.config('backup.backup.name').'/'.date('Y-m-d h:i') .'.sql';
        // \Spatie\DbDumper\Databases\MySql::create()
        //     ->setDbName(env('DB_DATABASE'))
        //     ->setUserName(env('DB_USERNAME'))
        //     ->setPassword(env('DB_PASSWORD'))
        //   // ->doNotCreateTables()
        //     //->dontSkipComments()
        //     ->dontUseExtendedInserts()
        //     ->useSingleTransaction()
        //     ->skipLockTables()
        //     ->dumpToFile($path);


            // // log the results
             Log::info("Backpack\BackupManager -- new backup started from admin interface " );
            
            //Manage No of Backup
             
            $business_id = request()->session()->get('user.business_id');
            $subscription = Subscription::active_subscription($business_id);
           if($subscription){
                $packageId=$subscription->package_id;
                $Package=Package::find($packageId);
                if($Package)
                {
                    $no_of_backup=$Package->no_of_backup;
                    
                     $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        
                     $files = $disk->files(config('backup.backup.name'));
                     $i=count($files);
                    // make an array of backup files, with their filesize and creation date
                    foreach ($files as $k => $f) {
                      
                        if($i>$no_of_backup)
                        {
                            $this->delete(str_replace(config('backup.backup.name') . '/', '', $f));
                        }
                        $i--;
                    }
                }
           }
            
            //end No of Backup
            $output = ['success' => 1,
                        'msg' => __('lang_v1.success')
                    ];
        } catch (Exception $e) {
            $output = ['success' => 0,
                        'msg' => $e->getMessage()
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

        //Disable in demo
        if (config('app.env') == 'demo') {
            $output = ['success' => 0,
                            'msg' => 'Feature disabled in demo!!'
                        ];
            return back()->with('status', $output);
        }

        $file = config('backup.backup.name') . '/' . $file_name;
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        if ($disk->exists($file)) {
            $fs = Storage::disk(config('backup.backup.destination.disks')[0])->getDriver();
            $stream = $fs->readStream($file);
            return \Response::stream(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                "Content-Type" => $fs->getMimetype($file),
                "Content-Length" => $fs->getSize($file),
                "Content-disposition" => "attachment; filename=\"" . basename($file) . "\"",
            ]);
        } else {
            abort(404, "The backup file doesn't exist.");
        }
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
