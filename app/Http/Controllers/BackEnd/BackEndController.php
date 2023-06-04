<?php

namespace App\Http\Controllers\BackEnd;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Faker\Provider\Image as ProviderImage;
use File,Carbon;
use Illuminate\Database\Eloquent\Model;

class BackEndController extends Controller
{

    protected $model;

    public function __construct(Model $model)
    {
          ini_set("memory_limit", "10056M");
        $this->model = $model;

    }

    public function index()
    {
        $rows = $this->model;
        $rows = $this->filter($rows);
        $with = $this->with();
        if (!empty($with)) {
            $rows = $rows->with($with);
        }
        if(request('limit') != null){
            $rows = $rows->orderBy('id', 'DESC')->take(request('limit'))->get();
        }
        else{
            $rows = $rows->orderBy('id', 'DESC')->get();
        }
        $moduleName = $this->pluralModelName();
        $sModuleName = $this->getModelName();
        $routeName = $this->getClassNameFromModel();
        $pageTitle = "Control " . $moduleName;
        $pageDes = "Here you can add / edit / delete / print " . $moduleName;
        $append = $this->appendIndex($rows);
        // return $rows;
        // return Auth::user()->role;
        return view('back-end.' . $routeName . '.index', compact(
            'rows',
            'pageTitle',
            'moduleName',
            'pageDes',
            'sModuleName',
            'routeName'
        ))->with($append);
    }

    public function create()
    {
        $moduleName = $this->getModelName();
        $pageTitle = "Create " . $moduleName;
        $pageDes = "Here you can create " . $moduleName;
        $folderName = $this->getClassNameFromModel();
        $routeName = $folderName;
        $append = $this->append(null);

        // return  request()->segment(3);
        return view('back-end.' . $folderName . '.create', compact(
            'pageTitle',
            'moduleName',
            'pageDes',
            'folderName',
            'routeName'
        ))->with($append);
    }
    public function show($id)
    {
        return redirect()->route($this->getClassNameFromModel() . '.index');
    }
    public function destroy($id)
    {
        $this->model->FindOrFail($id)->delete();
        $this->destoryRelationShip($id);
        return redirect()->route($this->getClassNameFromModel() . '.index');
    }

    public function edit($id)
    {
        // return Auth::user()->role;
        $row = $this->model->FindOrFail($id);
        $moduleName = $this->getModelName();
        $pageTitle = "Edit " . $moduleName;
        $pageDes = "Here you can edit " . $moduleName;
        $folderName = $this->getClassNameFromModel();
        $routeName = $folderName;
        $append = $this->append($row);
        // return $this->model;
        // return $append;
        return view('back-end.' . $folderName . '.edit', compact(
            'row',
            'pageTitle',
            'moduleName',
            'pageDes',
            'folderName',
            'routeName'
        ))->with($append);
    }
    public function getDownload($path)
    {
        //PDF file is stored under project/public/download/info.pdf
        $file = public_path() . "/download/info.pdf";

        $headers = array(
            'Content-Type: application/pdf',
        );

        return Response::download($file, 'filename.pdf', $headers);
    }
    protected function uploadImage($request, $height = 400, $width = 400)
    {

        $photo = $request->file('image');
        $fileName = time() . str_random('10') . '.' . $photo->getClientOriginalExtension();
        $destinationPath = 'uploads/' . $this->getClassNameFromModel() . '/';
        $image = Image::make($photo->getRealPath())->resize($height, $width);

        // return $destinationPath;

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath);
        }
        $image->save($destinationPath . $fileName);
        return 'uploads/' . $this->getClassNameFromModel() . '/' . $fileName;
    }
    protected function storeFile($file)
    {
        $mytime = Carbon\Carbon::now();
        $file = Input::file('file');
        $path = '/' . $this->pluralModelName();
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        $name = $this->pluralModelName() . ' ' . $mytime->toDateTimeString() . '.' . $file->getClientOriginalExtension();

        $name = str_replace(' ', '_', $name);
        $name = str_replace(':', '_', $name);
        // $destinationPath = public_path('/' . $this->pluralModelName().'/');
        $destinationPath = public_path();

        $file->move($destinationPath, $name);
        return $this->pluralModelName() . '/' . $name;
    }

    protected function filter($rows)
    {
        return $rows;
    }

    protected function with()
    {
        return [];
    }

    protected function getClassNameFromModel()
    {
        return strtolower($this->pluralModelName());
    }

    protected function pluralModelName()
    {
        return str_plural($this->getModelName());
    }

    protected function getModelName()
    {
        return class_basename($this->model);
    }

    protected function destoryRelationShip($id)
    {
        return [];
    }

    protected function append($row)
    {
        return [];
    }
    protected function appendIndex($rows){
        return [];
    }
}
