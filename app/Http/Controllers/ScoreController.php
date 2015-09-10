<?php namespace App\Http\Controllers;

use App\Exam;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Score;
use App\Student;
use Illuminate\Http\Request;
use Input;

class ScoreController extends Controller {

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($classID, $examID)
	{

		//return Score::with(['student'=>function($q){$q->addSelect(['id','name']);}])
         //   ->where('exam_id', $examID)->get();

        // 選擇 id num name 欄位
        // 篩選出具有指定測驗之成績的學生
        //　eager load 成績紀錄
        // 依 num 遞增排序
        return Student::select('id', 'num', 'name')
            ->whereHas(
                'scores',function($q) use ($examID)
                    {
                        $q->where('exam_id', $examID);
                    }
            )
            ->with(['scores' => function($q) use ($examID){
                $q->where('exam_id', $examID);
            }])->orderBy('num')->get();

        //return Student::select('id', 'num', 'name')
        //              ->whereHas(
        //                  'scores',function($q) use ($examID)
        //              {
        //                  $q->where('exam_id', $examID);
        //              }
        //              )
        //              ->with(['scores' => function($q) use ($examID){
        //                  $q->where('exam_id', $examID);
        //              }])->where('class_id', $classID)->orderBy('num')->get();

        //return Exam::with(['scores.student'=>function($q){$q->addSelect(['id','name']);}])->find($examID)->scores;


	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($classID, $examID)
	{
        $newModels = [];
		$data = Input::all();
        $exam = Exam::with('scores')->find($examID);

        //取得已存在之紀錄之id陣列，刪除之
        if($exist_scores = $exam->scores->lists('id')){
            Score::destroy($exist_scores);
        }

        foreach($data as $item){
            $student['num'] = array_pull($item, 'num');
            $student['name'] = array_pull($item, 'name');
            $item['student_id'] = Student::where('class_id', $classID)->where('num', $student['num'])->first()->id;
            $newModels[] = new Score($item);
        }

        $rows_count = count($exam->scores()->saveMany($newModels));

        $messages[] = ['type' => 'success', 'content' => '成績匯入成功，共 '. $rows_count .' 筆紀錄'];

        return response()->json(["messages" => $messages], 200);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
