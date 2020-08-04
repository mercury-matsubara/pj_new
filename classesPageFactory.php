<?php

/**
 * 遷移先の判定を行い、Pageオブジェクトを生成する
 */
class PageFactory
{
	protected static $factory;
	public $pbFormIni;
	/**
	 * コンストラクタ
	 */
	protected function __construct()
	{
		$this->pbFormIni = parse_ini_file('./ini/form.ini', true);
		require_once("classesPageContainer.php");
		require_once("classesExecute.php");
	}
	
   /**
    * インスタンス生成と取得用関数
    * 
     * @return PageFactoryインスタンス
    */
	public static function getInstance()
	{
		if(!isset(self::$factory))
		{
			self::$factory = new PageFactory();
		}
		return self::$factory;
	}
	
    /**
    * Pageオブジェクトの生成
    * 
    * @param string $filename ページID指定文字列
    * @return BasePage
    */
	public function createPage($filename,$container)
	{
		$page = null;
		$pre_url = explode('_',$filename);
		
		//画面判定変数
		$step = $container->pbStep;
		
		//--↓↓↓↓↓ワンオフの特殊ページ↓↓↓↓↓--
                if($filename === 'GETSUZI_5')
                {
                    $page = new Getsuzi($container);
                }
                else if($filename === 'NENZI_5')
                {
                    $page = new Nenzi($container);
                }
                else if($filename === "PJEND_2")
                {
                    $page = new Pjend($container);
                }
                else if($filename === "PJCANCEL_2")
                {
                    $page = new Pjcancel($container);
                }
                else if($filename === 'PJICHIRAN_1')
                {
                    $page = new Pjinsert($container);
                }
                else if($filename === 'PROGRESSINFO_1')
                {
                    $page = new Progress($container);
                }
                else if($filename === 'STAFFMONEYSET_2')
                {
                    $page = new StaffMoneySet($container);
                }
                else if($filename === "PJNUMPOPUP_2")
                {
                    $page = new PjInsertPopup($container);
                }
                if($page !== null)
		{
			return $page;
		}
		//--↑↑↑↑↑ワンオフの特殊ページ↑↑↑↑↑--
		
		//汎用ページ
		if($pre_url[1] === '1')//登録、編集
		{
			if($step == STEP_INSERT)//データ登録
			{
                            $page = new InsertPage($container);
			}
			else if($step == STEP_EDIT)//データ編集
			{
                                $page = new EditPage($container);
			}
			else if($step == STEP_DELETE)//データ削除
			{
				$page = new DeletePage($container);
			}
		}
		else if($pre_url[1] === '2')//リスト
		{
			$page = new ListPage($container);
		}
		else if($pre_url[1] === '3')//編集のみ
		{
			/*if($container->pbPageCheck === 'Execute')//データ処理
			{
				$page = new BaseLogicExecuter($container);
			}
			else
			{*/
                     $page = new EditPage($container);
//}
			
		}	
		else if($pre_url[1] === '5')//印刷
		{
			
		}
		else if($pre_url[1] === '6')//条件指定
		{
			$page = new CondisionPage($container);
		}
		else
		{
			$page = new TopPage($container);			
		}
		
                if($filename === 'TOP_5') 
		{
			$page = new TopPage($container);
		}
                
		return $page;
	}
	
	/**
    * データ処理オブジェクトの生成
    * 
    * @param string $filename
    * @return BaseLogicExecuter
    */
	public function createExecuter($filename,$container)
	{
		$executer = null;
		
		
		if($container->pbPageCheck === 'Execute')//データ処理
		{
			//--特殊処理--//
			if($filename === 'EDABANMASTER_1')
			{
				$executer = new EdabanInsertExecuteSQL($container);
			}
                        else if($filename === 'PJEND_2')
			{
				$executer = new PjendExecuteSQL($container);
			}
                        else if($filename === 'PJCANCEL_2')
			{
				$executer = new PjcancelExecuteSQL($container);
			}
                        else if($filename === 'GETSUZI_5')
                        {
                                $executer = new GetsuziExecuteSQL($container);
                        }
                        else if($filename === 'NENZI_5')
                        {
                                $executer = new NenziExecuteSQL($container);
                        }
                        else if($filename === 'PJICHIRAN_1')
                        {
                                $executer = new PjtourokuExecuteSQL($container);
                        }
                        else if($filename === 'PROGRESSINFO_1' || $filename === 'PROGRESSINFO_3')
                        {
                                $executer = new ProgressExecuteSQL($container);
                        }
                        else if($filename === 'STAFFMONEYSET_2')
                        {
                                $executer = new StaffMoneySetExecuteSQL($container);
                        }
                        if($executer !== null)
			{
				return $executer;
			}
			//--特殊処理--//
			
			$executer = new BaseLogicExecuter($container);
		}
		
		return $executer;
	}		
}
