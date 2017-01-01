<!-- Content Header (Page header) -->
<section class="content-header" style="background: #FFFFFF;">
    <div class="row" >
        <div class="col-xs-6 pull-left " ><h4 class="text-primary">Dashboard<?php //echo _string_human($this->router->fetch_class())   ?></h4></div>
    </div>
</section>

<?php
//
//$pemakaian = 24;
//$batas = array(
//    "30" => "500",
//    "20" => "350",
//    "10" => "300",
//    "0" => "250",
//);
//
//$tempPemakaian = $pemakaian;
//echo "pemakaian total ".$pemakaian."<br>";
//$harga = 0;
//$pengurang = 0;
//$total = 0;
//foreach ($batas as $max => $tarif) {
//    if ( $tempPemakaian > $max ){
//        $pengurang = $tempPemakaian - $max;
//    } else {
//        continue;
//    }
//
//    $tempPemakaian -= $pengurang;
//    $harga = $pengurang * $tarif;
//    $total += $harga;
//    echo "pemakaian ". $pengurang . " tarif ". $tarif ." -> ". $harga ."<br>";
//}
//echo $total;
?>

<!--<section class="content">
    <div class="row">
        <div class="col-lg-3 col-xs-6">
             small box 
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>
                        150
                    </h3>
                    <p>
                        New Orders
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="#" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div> ./col 
        <div class="col-lg-3 col-xs-6">
             small box 
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>
                        53<sup style="font-size: 20px">%</sup>
                    </h3>
                    <p>
                        Bounce Rate
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="#" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div> ./col 
        <div class="col-lg-3 col-xs-6">
             small box 
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>
                        44
                    </h3>
                    <p>
                        User Registrations
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="#" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div> ./col 
        <div class="col-lg-3 col-xs-6">
             small box 
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>
                        65
                    </h3>
                    <p>
                        Unique Visitors
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="#" class="small-box-footer">
                    More info <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div> ./col 
    </div>
    <div class="row">
        <div class="col-md-6">
             AREA CHART 
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Area Chart</h3>
                </div>
                <div class="box-body chart-responsive">
                    <div class="chart" id="revenue-chart" style="height: 300px;"><svg style="overflow: hidden; position: relative; top: -0.399994px;" xmlns="http://www.w3.org/2000/svg" width="515" version="1.1" height="300"><desc>Created with Raphaël 2.1.0</desc><defs></defs><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="260" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">0</tspan></text><path stroke-width="0.5" d="M66,260H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="201.25" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">7,500</tspan></text><path stroke-width="0.5" d="M66,201.25H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="142.5" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">15,000</tspan></text><path stroke-width="0.5" d="M66,142.5H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="83.75" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">22,500</tspan></text><path stroke-width="0.5" d="M66,83.75H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="25.00000000000003" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.500000000000028">30,000</tspan></text><path stroke-width="0.5" d="M66,25.00000000000003H490" stroke="#aaaaaa" fill="none" style=""></path><text transform="matrix(1,0,0,1,0,7.5)" font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="272.5" x="412.20656136087484" style="text-anchor: middle; font: 12px sans-serif;"><tspan dy="4.5">2013</tspan></text><text transform="matrix(1,0,0,1,0,7.5)" font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="272.5" x="223.6476306196841" style="text-anchor: middle; font: 12px sans-serif;"><tspan dy="4.5">2012</tspan></text><path fill-opacity="1" d="M66,218.23266666666666C77.84933171324423,218.74183333333332,101.54799513973269,221.7860625,113.39732685297692,220.26933333333335C125.24665856622114,218.75260416666669,148.9453219927096,208.35534699453552,160.79465370595383,206.09883333333335C172.51518833535846,203.86684699453554,195.95625759416768,204.11993750000002,207.6767922235723,202.31533333333334C219.39732685297693,200.51072916666666,242.83839611178615,194.192947859745,254.55893074119078,191.662C266.40826245443503,189.10323952641167,290.1069258809235,181.84977083333334,301.9562575941677,181.9565C313.80558930741194,182.06322916666667,337.5042527339004,203.4213315118397,349.3535844471446,192.51583333333332C361.0741190765492,181.72887317850638,384.51518833535846,101.61791988950276,396.23572296476306,95.18666666666667C407.8274605103281,88.82608655616943,431.0109356014581,134.67133653846153,442.6026731470231,141.3485C454.4520048602673,148.17404487179488,478.1506682867558,147.23525,490,149.1975L490,260L66,260Z" stroke="none" fill="#74a5c1" style="fill-opacity: 1;"></path><path stroke-width="3" d="M66,218.23266666666666C77.84933171324423,218.74183333333332,101.54799513973269,221.7860625,113.39732685297692,220.26933333333335C125.24665856622114,218.75260416666669,148.9453219927096,208.35534699453552,160.79465370595383,206.09883333333335C172.51518833535846,203.86684699453554,195.95625759416768,204.11993750000002,207.6767922235723,202.31533333333334C219.39732685297693,200.51072916666666,242.83839611178615,194.192947859745,254.55893074119078,191.662C266.40826245443503,189.10323952641167,290.1069258809235,181.84977083333334,301.9562575941677,181.9565C313.80558930741194,182.06322916666667,337.5042527339004,203.4213315118397,349.3535844471446,192.51583333333332C361.0741190765492,181.72887317850638,384.51518833535846,101.61791988950276,396.23572296476306,95.18666666666667C407.8274605103281,88.82608655616943,431.0109356014581,134.67133653846153,442.6026731470231,141.3485C454.4520048602673,148.17404487179488,478.1506682867558,147.23525,490,149.1975" stroke="#3c8dbc" fill="none" style=""></path><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="218.23266666666666" cx="66"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="220.26933333333335" cx="113.39732685297692"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="206.09883333333335" cx="160.79465370595383"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="202.31533333333334" cx="207.6767922235723"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="191.662" cx="254.55893074119078"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="181.9565" cx="301.9562575941677"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="192.51583333333332" cx="349.3535844471446"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="95.18666666666667" cx="396.23572296476306"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="141.3485" cx="442.6026731470231"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="149.1975" cx="490"></circle><path fill-opacity="1" d="M66,239.11633333333333C77.84933171324423,238.897,101.54799513973269,240.43820833333334,113.39732685297692,238.239C125.24665856622114,236.03979166666667,148.9453219927096,222.49635428051002,160.79465370595383,221.52266666666668C172.51518833535846,220.55956261384335,195.95625759416768,232.3502916666667,207.6767922235723,230.49183333333335C219.39732685297693,228.633375,242.83839611178615,208.50817190346083,254.55893074119078,206.655C266.40826245443503,204.7814635701275,290.1069258809235,213.63645833333334,301.9562575941677,215.585C313.80558930741194,217.53354166666668,337.5042527339004,231.50074954462661,349.3535844471446,222.24333333333334C361.0741190765492,213.08654121129328,384.51518833535846,147.70467656537753,396.23572296476306,141.92816666666667C407.8274605103281,136.21513489871086,431.0109356014581,169.85397847985348,442.6026731470231,176.28516666666667C454.4520048602673,182.85927014652015,478.1506682867558,189.53329166666668,490,193.94933333333336L490,260L66,260Z" stroke="none" fill="#eaf2f5" style="fill-opacity: 1;"></path><path stroke-width="3" d="M66,239.11633333333333C77.84933171324423,238.897,101.54799513973269,240.43820833333334,113.39732685297692,238.239C125.24665856622114,236.03979166666667,148.9453219927096,222.49635428051002,160.79465370595383,221.52266666666668C172.51518833535846,220.55956261384335,195.95625759416768,232.3502916666667,207.6767922235723,230.49183333333335C219.39732685297693,228.633375,242.83839611178615,208.50817190346083,254.55893074119078,206.655C266.40826245443503,204.7814635701275,290.1069258809235,213.63645833333334,301.9562575941677,215.585C313.80558930741194,217.53354166666668,337.5042527339004,231.50074954462661,349.3535844471446,222.24333333333334C361.0741190765492,213.08654121129328,384.51518833535846,147.70467656537753,396.23572296476306,141.92816666666667C407.8274605103281,136.21513489871086,431.0109356014581,169.85397847985348,442.6026731470231,176.28516666666667C454.4520048602673,182.85927014652015,478.1506682867558,189.53329166666668,490,193.94933333333336" stroke="#a0d0e0" fill="none" style=""></path><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="239.11633333333333" cx="66"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="238.239" cx="113.39732685297692"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="221.52266666666668" cx="160.79465370595383"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="230.49183333333335" cx="207.6767922235723"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="206.655" cx="254.55893074119078"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="215.585" cx="301.9562575941677"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="222.24333333333334" cx="349.3535844471446"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="141.92816666666667" cx="396.23572296476306"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="176.28516666666667" cx="442.6026731470231"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#a0d0e0" r="4" cy="193.94933333333336" cx="490"></circle></svg><div style="left: 161.177px; top: 134px; display: none;" class="morris-hover morris-default-style"><div class="morris-hover-row-label">2011 Q4</div><div class="morris-hover-point" style="color: #a0d0e0">
                                Item 1:
                                3,767
                            </div><div class="morris-hover-point" style="color: #3c8dbc">
                                Item 2:
                                3,597
                            </div></div></div>
                </div> /.box-body 
            </div> /.box 

             DONUT CHART 
            <div class="box box-danger">
                <div class="box-header">
                    <h3 class="box-title">Donut Chart</h3>
                </div>
                <div class="box-body chart-responsive">
                    <div class="chart" id="sales-chart" style="height: 300px; position: relative;"><svg style="overflow: hidden; position: relative; top: -0.400024px;" xmlns="http://www.w3.org/2000/svg" width="515" version="1.1" height="300"><desc>Created</desc><defs></defs><path opacity="0" stroke-width="2" d="M257.5,243.33333333333331A93.33333333333333,93.33333333333333,0,0,0,345.7277551949771,180.44625304313007" stroke="#3c8dbc" fill="none" style="opacity: 0;"></path><path stroke-width="3" d="M257.5,246.33333333333331A96.33333333333333,96.33333333333333,0,0,0,348.56364732624417,181.4248826052307L385.1151459070204,194.03833029452744A135,135,0,0,1,257.5,285Z" stroke="#ffffff" fill="#3c8dbc" style=""></path><path opacity="1" stroke-width="2" d="M345.7277551949771,180.44625304313007A93.33333333333333,93.33333333333333,0,0,0,173.78484627831412,108.73398312817662" stroke="#f56954" fill="none" style="opacity: 1;"></path><path stroke-width="3" d="M348.56364732624417,181.4248826052307A96.33333333333333,96.33333333333333,0,0,0,171.09400205154566,107.40757544301087L131.92726941747117,88.10097469226493A140,140,0,0,1,389.8416327924656,195.6693795646951Z" stroke="#ffffff" fill="#f56954" style=""></path><path opacity="0" stroke-width="2" d="M173.78484627831412,108.73398312817662A93.33333333333333,93.33333333333333,0,0,0,257.47067846904883,243.333328727518" stroke="#00a65a" fill="none" style="opacity: 0;"></path><path stroke-width="3" d="M171.09400205154566,107.40757544301087A96.33333333333333,96.33333333333333,0,0,0,257.46973599126824,246.3333285794739L257.4575884998742,284.9999933380171A135,135,0,0,1,136.4120097954186,90.31165416754118Z" stroke="#ffffff" fill="#00a65a" style=""></path><text stroke-width="0.6130952380952381" transform="matrix(1.6311,0,0,1.6311,-162.8155,-94.0291)" font-weight="800" font-size="15px" fill="#000000" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="140" x="257.5" style="text-anchor: middle; font: 800 15px 'Arial';"><tspan dy="5">In-Store Sales</tspan></text><text stroke-width="0.5142857142857143" transform="matrix(1.9444,0,0,1.9444,-243.6667,-143.5556)" font-size="14px" fill="#000000" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="160" x="257.5" style="text-anchor: middle; font: 14px 'Arial';"><tspan dy="5">30</tspan></text></svg></div>
                </div> /.box-body 
            </div> /.box 

        </div> /.col (LEFT) 
        <div class="col-md-6">
             LINE CHART 
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Line Chart</h3>
                </div>
                <div class="box-body chart-responsive">
                    <div class="chart" id="line-chart" style="height: 300px;"><svg style="overflow: hidden; position: relative; left: -0.5px; top: -0.399994px;" xmlns="http://www.w3.org/2000/svg" width="515" version="1.1" height="300"><desc>Created with  2.1.0</desc><defs></defs><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="260" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">0</tspan></text><path stroke-width="0.5" d="M66,260H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="201.25" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">5,000</tspan></text><path stroke-width="0.5" d="M66,201.25H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="142.5" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">10,000</tspan></text><path stroke-width="0.5" d="M66,142.5H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="83.75" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">15,000</tspan></text><path stroke-width="0.5" d="M66,83.75H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="25" x="53.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">20,000</tspan></text><path stroke-width="0.5" d="M66,25H490" stroke="#aaaaaa" fill="none" style=""></path><text transform="matrix(1,0,0,1,0,7.5)" font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="272.5" x="412.20656136087484" style="text-anchor: middle; font: 12px sans-serif;"><tspan dy="4.5">2013</tspan></text><text transform="matrix(1,0,0,1,0,7.5)" font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="272.5" x="223.6476306196841" style="text-anchor: middle; font: 12px sans-serif;"><tspan dy="4.5">2012</tspan></text><path stroke-width="3" d="M66,228.6745C77.84933171324423,228.3455,101.54799513973269,230.6573125,113.39732685297692,227.3585C125.24665856622114,224.0596875,148.9453219927096,203.74453142076501,160.79465370595383,202.284C172.51518833535846,200.83934392076503,195.95625759416768,218.5254375,207.6767922235723,215.73775C219.39732685297693,212.9500625,242.83839611178615,182.76225785519128,254.55893074119078,179.98250000000002C266.40826245443503,177.17219535519126,290.1069258809235,190.4546875,301.9562575941677,193.3775C313.80558930741194,196.3003125,337.5042527339004,217.2511243169399,349.3535844471446,203.365C361.0741190765492,189.6298118169399,384.51518833535846,91.55701484806629,396.23572296476306,82.89224999999999C407.8274605103281,74.32270234806629,431.0109356014581,124.78096771978022,442.6026731470231,134.42775C454.4520048602673,144.28890521978022,478.1506682867558,154.2999375,490,160.924" stroke="#3c8dbc" fill="none" style=""></path><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="228.6745" cx="66"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="227.3585" cx="113.39732685297692"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="202.284" cx="160.79465370595383"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="215.73775" cx="207.6767922235723"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="179.98250000000002" cx="254.55893074119078"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="193.3775" cx="301.9562575941677"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="203.365" cx="349.3535844471446"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="82.89224999999999" cx="396.23572296476306"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="134.42775" cx="442.6026731470231"></circle><circle stroke-width="1" style="" stroke="#ffffff" fill="#3c8dbc" r="4" cy="160.924" cx="490"></circle></svg><div style="left: 422px; top: 86px; display: none;" class="morris-hover morris-default-style"><div class="morris-hover-row-label">2013 Q2</div><div class="morris-hover-point" style="color: #3c8dbc">
                                Item 1:
                                8,432
                            </div></div></div>
                </div> /.box-body 
            </div> /.box 

             BAR CHART 
            <div class="box box-success">
                <div class="box-header">
                    <h3 class="box-title">Bar Chart</h3>
                </div>
                <div class="box-body chart-responsive">
                    <div class="chart" id="bar-chart" style="height: 300px;"><svg style="overflow: hidden; position: relative; left: -0.5px; top: -0.400024px;" xmlns="http://www.w3.org/2000/svg" width="515" version="1.1" height="300"><desc>Created with  2.1.0</desc><defs></defs><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="260" x="36.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">0</tspan></text><path stroke-width="0.5" d="M49,260H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="201.25" x="36.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">25</tspan></text><path stroke-width="0.5" d="M49,201.25H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="142.5" x="36.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">50</tspan></text><path stroke-width="0.5" d="M49,142.5H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="83.75" x="36.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">75</tspan></text><path stroke-width="0.5" d="M49,83.75H490" stroke="#aaaaaa" fill="none" style=""></path><text font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="end" y="25" x="36.5" style="text-anchor: end; font: 12px sans-serif;"><tspan dy="4.5">100</tspan></text><path stroke-width="0.5" d="M49,25H490" stroke="#aaaaaa" fill="none" style=""></path><text transform="matrix(1,0,0,1,0,7.5)" font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="272.5" x="458.5" style="text-anchor: middle; font: 12px sans-serif;"><tspan dy="4.5">2012</tspan></text><text transform="matrix(1,0,0,1,0,7.5)" font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="272.5" x="332.5" style="text-anchor: middle; font: 12px sans-serif;"><tspan dy="4.5">2010</tspan></text><text transform="matrix(1,0,0,1,0,7.5)" font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="272.5" x="206.5" style="text-anchor: middle; font: 12px sans-serif;"><tspan dy="4.5">2008</tspan></text><text transform="matrix(1,0,0,1,0,7.5)" font-weight="normal" font-family="sans-serif" font-size="12px" fill="#888888" stroke="none" font="10px &quot;Arial&quot;" text-anchor="middle" y="272.5" x="80.5" style="text-anchor: middle; font: 12px sans-serif;"><tspan dy="4.5">2006</tspan></text><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#00a65a" ry="0" rx="0" r="0" height="235" width="22.125" y="25" x="56.875"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#f56954" ry="0" rx="0" r="0" height="211.5" width="22.125" y="48.5" x="82"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#00a65a" ry="0" rx="0" r="0" height="176.25" width="22.125" y="83.75" x="119.875"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#f56954" ry="0" rx="0" r="0" height="152.75" width="22.125" y="107.25" x="145"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#00a65a" ry="0" rx="0" r="0" height="117.5" width="22.125" y="142.5" x="182.875"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#f56954" ry="0" rx="0" r="0" height="94" width="22.125" y="166" x="208"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#00a65a" ry="0" rx="0" r="0" height="176.25" width="22.125" y="83.75" x="245.875"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#f56954" ry="0" rx="0" r="0" height="152.75" width="22.125" y="107.25" x="271"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#00a65a" ry="0" rx="0" r="0" height="117.5" width="22.125" y="142.5" x="308.875"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#f56954" ry="0" rx="0" r="0" height="94" width="22.125" y="166" x="334"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#00a65a" ry="0" rx="0" r="0" height="176.25" width="22.125" y="83.75" x="371.875"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#f56954" ry="0" rx="0" r="0" height="152.75" width="22.125" y="107.25" x="397"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#00a65a" ry="0" rx="0" r="0" height="235" width="22.125" y="25" x="434.875"></rect><rect fill-opacity="1" style="fill-opacity: 1;" stroke="none" fill="#f56954" ry="0" rx="0" r="0" height="211.5" width="22.125" y="48.5" x="460"></rect></svg><div style="display: none;" class="morris-hover morris-default-style"></div></div>
                </div> /.box-body 
            </div> /.box 

        </div> /.col (RIGHT) 
    </div> /.row 

</section>-->