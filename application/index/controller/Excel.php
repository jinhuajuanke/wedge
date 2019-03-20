<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
header("Content-Type:text/html;charset=utf8");
class Excel extends Allow
{
    //从collect/index页面导出所有数据
    public function export(){
        $xlsData = Db::name('company')->where('company_name','like','%'.input('keyword').'%')->order(input('order'))->limit(input('limit'))->select();

        Vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        Vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        Vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F");
        $arrHeader = array('公司名称','联系人','手机号','QQ','公司描述','备注');
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        };
        //填充表格信息
        foreach($xlsData as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['company_name']);
            $objActSheet->setCellValue('B'.$k, $v['linkman']);
            $objActSheet->setCellValue('C'.$k, $v['telphone']);
            $objActSheet->setCellValue('D'.$k, $v['qq']);
            $objActSheet->setCellValue('E'.$k, $v['description']);
            $objActSheet->setCellValue('F'.$k, $v['notes']);
            
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }

 

        $width = array(30,15,20,15,90,40);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[0]);
        $objActSheet->getColumnDimension('B')->setWidth($width[1]);
        $objActSheet->getColumnDimension('C')->setWidth($width[2]);
        $objActSheet->getColumnDimension('D')->setWidth($width[3]);
        $objActSheet->getColumnDimension('E')->setWidth($width[4]);
        $objActSheet->getColumnDimension('F')->setWidth($width[5]);

        $outfile = "公司信息列表.xlsx";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    //从收藏页面导出数据
    public function exportStow(){
        $stowtype=input('stowtype');
        if(!empty($stowtype)){
            $map['stowtype']=$stowtype;
        }
        $map['mid']=session('userid');
        $limit=input("limit");
        $limits=9999999;
        if(!empty($limit)){
            $limits=$limit;
        }
        $order=input("order");
        if(!empty($order)){
            $orders=$order;
        }
        $xlsData = Db::name('company')->where($map)->order($orders)->limit($limits)->select();
        Vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        Vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        Vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F");
        $arrHeader = array('公司名称','联系人','手机号','QQ','公司描述','备注');
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        };
        //填充表格信息
        foreach($xlsData as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['company_name']);
            $objActSheet->setCellValue('B'.$k, $v['linkman']);
            $objActSheet->setCellValue('C'.$k, $v['telphone']);
            $objActSheet->setCellValue('D'.$k, $v['qq']);
            $objActSheet->setCellValue('E'.$k, $v['description']);
            $objActSheet->setCellValue('F'.$k, $v['notes']);

            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }

 
        $width = array(30,15,20,15,90,40);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[0]);
        $objActSheet->getColumnDimension('B')->setWidth($width[1]);
        $objActSheet->getColumnDimension('C')->setWidth($width[2]);
        $objActSheet->getColumnDimension('D')->setWidth($width[3]);
        $objActSheet->getColumnDimension('E')->setWidth($width[4]);
        $objActSheet->getColumnDimension('F')->setWidth($width[5]);


        $outfile = "公司信息列表.xlsx";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    //导入excel文件
    public function import(){

        Vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        Vendor('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory');//调用类库,路径是基于vendor文件夹的
        Vendor('PHPExcel.PHPExcel.Reader.Excel5');//调用类库,路径是基于vendor文件夹的

        $request = \think\Request::instance();
        $file = $request->file('excel');
        if($file){

            $save_path = ROOT_PATH . 'public/excel/';
            $info = $file->validate(['size'=>5242880,'ext'=>'xlsx,xls'])->move($save_path);

        //print_r($info);exit;
        $filename=$save_path . DIRECTORY_SEPARATOR . $info->getSaveName(); 
        if(file_exists($filename)) {
            // var_dump(strstr($filename,'.xls'));exit();
        //如果文件存在
            if(strstr($filename,'.xlsx')) {
                $PHPReader = new \PHPExcel_Reader_Excel2007(); 
            } else {
                $PHPReader = new \PHPExcel_Reader_Excel5(); 
            }

            //载入excel文件
            $PHPExcel = $PHPReader->load($filename); 
            $sheet = $PHPExcel->getActiveSheet(0);//获得sheet 
            $highestRow = $sheet->getHighestRow(); // 取得共有数据数 
            $data=$sheet->toArray(); 
            //从数据库获取数据
            for($i=0;$i<$highestRow;$i++){ 
                $user['company_name']=trim($data[$i][0]);
                $user['linkman']=trim($data[$i][1]);
                $user['telphone']=trim($data[$i][2]);
                $user['qq']=trim($data[$i][3]);
                $user['guhua']=trim($data[$i][4]);
                $user['register_time']=trim($data[$i][5]);
                $user['address']=trim($data[$i][6]);

                $map['company_name']=trim($data[$i][0]);
                $map['linkman']=trim($data[$i][1]);
            //第二栏 
                $data_list=Db::name("company")->where($map)->find();
                if(!$data_list){
                   $new_datas[] = $user; 
                } else {
                   $new_datas = ''; 
                }

            }

            // var_dump($new_datas);exit();
            if(!empty($new_datas)){
                db('company')->insertAll($new_datas, true); 
                $this->success('成功上传' . count($new_datas) . '条数据。有'.($highestRow-count($new_datas)).'无效数据。','collect/index'); 
            }else{ 
                $this->error('有'.($highestRow-count($new_datas)).'条无效数据。'); 
            } 

        // return $ret; 
        }else{ 
            return array("resultcode" => -5, "resultmsg" => "文件不存在", "data" => null); 
        } 
        }else {
            $this->error("上传失败！");
        }
    }


}





?>