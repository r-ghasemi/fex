<?php
include('fex-classes.php');

define( 'B_BEGIN', "{");
define( 'B_END', "}");

define( 'BR_BEGIN', "[");
define( 'BR_END', "]");

define( 'P_BEGIN', "(");
define( 'P_END', ")");
define( 'FEX_DOC', "\\DOC");
define( 'FEX_CMD', "\\newcommand");
define( 'FEX_CALL', "\\فراخوان");
define( 'FEX_IF', "\\if");
define( 'FEX_REPEAT', "\\repeat");
define( 'FEX_VALUE', "\\value");
define( 'FEX_EVAL', "\\php");
define( 'FEX_STYLE', "\\style\\");
define( 'EOF', "");

function error($t) {
	echo "\nخطا: $t\n";
	die ();
}


function style($p2) {
	$style='';
	if ($p2!='')   $style ="style='$p2'";
	return $style;
}

$jump[FEX_CMD]=$jump['\newcommand']= new fexNewCommand("\\newcommand", []);
$jump[FEX_CALL]=$jump['\include']= new fexCallCommand("\include", []);
$jump[FEX_STYLE]= new fexStyleCommand("\style", []);
$jump['\asis']= new fexAsIsCommand("\\asis", []);
$jump[FEX_DOC]= new fexDocCommand("\doc", []);
$jump[FEX_REPEAT]=$jump['\repeat']= new fexRepeatCommand("\\repeat",[]);
$jump['\foreach']= new fexForeachCommand("\\foreach",[]);
$jump['\for']= new fexForCommand("\\for",[]);
$jump['\while']= new fexWhileCommand("\\while",[]);
$jump['\\switch']= new fexSwitchCommand("\\switch",[]);
$jump['\\case']  = new fexCaseCommand("\\case",[]);
$jump['\\default']  = new fexDefaultCommand("\\default",[]);
$jump[FEX_IF]=$jump['\\if']= new fexIfCommand("\\if", []);

function splitCommand($cmd) {
	$s=array();
	$c=0;
	$ix=0;
	$s[$c]='';
	while ($ix<strlen($cmd)) {
		if(strpos(".#~$",$cmd[$ix])===FALSE) 
			$s[$c] .= $cmd[$ix];
		else { $c++; $s[$c]=$cmd[$ix]; };
		$ix++;
	}
	return $s;
}

class fex {
	public $input;
	public $p0s;
	
	function __construct ($i) {
		$this->input= "{" . $i . "}\n";
		$this->pos=0;
	}
	
	function getToken() {
		$token='';
		
		do {
	    	while ($this->pos < strlen($this->input) 
    			&& ($this->input[$this->pos]==' '
			    || $this->input[$this->pos]=="\n"
			    || $this->input[$this->pos]=="\t"
			    ))	$this->pos++;
		    
		    if ($this->pos >= strlen($this->input) 
		        || $this->input[$this->pos]!="%") break;
		    
		    //comment			
			while ($this->pos < strlen($this->input) && $this->input[$this->pos]!="\n")  
				$this->pos++;
			
		} while (1);

		if ($this->pos >= strlen($this->input))  return EOF;
		
		if ($this->input[$this->pos]== P_BEGIN ) { //command
			$this->pos++;
			return P_BEGIN;
		} else 		
		if ($this->input[$this->pos]== P_END ) { //command
			$this->pos++;
			return P_END;
		} else 		
		if ($this->input[$this->pos]== B_END ) { //command
			$this->pos++;
			return B_END;
		} else 
		if ($this->input[$this->pos]== B_BEGIN) { //command
			$this->pos++;
			return B_BEGIN;
		} else
		if ($this->input[$this->pos]=='\\') { //command
			$this->pos++;
			if (strpos(" /\&{}$^*@", $this->input[$this->pos])!==FALSE) {
				$token= '\\' . $this->input[$this->pos];
				
				$this->pos++;
				return $token;
			}
			$token='\\';
			while ($this->pos< strlen($this->input) && 
					($this->input[$this->pos]!=B_BEGIN 
					&& $this->input[$this->pos]!=' ' 
					&& $this->input[$this->pos]!="\n" 
					&& $this->input[$this->pos]!="\t")) {
				if ($this->input[$this->pos]==P_BEGIN) break;
				$token .= $this->input[$this->pos];
				$this->pos++;
			}
			return $token;
		} else {
			while ($this->pos< strlen($this->input)) {
				if ($this->input[$this->pos]=='{') break;
				if ($this->input[$this->pos]=='}') break;
				if ($this->input[$this->pos]=="\\") break;
				//if ($this->input[$this->pos]=="%") break;
				if ($this->input[$this->pos]==")") break;
				if ($this->input[$this->pos]=="(") break;
				if ($this->input[$this->pos]=="\n") break;
		//		if ($this->input[$this->pos]=="<") break;
				//if ($this->input[$this->pos]==" ") break;
				//if ($this->input[$this->pos]=="\t") break;
			
				$token .= $this->input[$this->pos];
				$this->pos++;
			}
			return $token;
		}
	}
	
	function parse($cmd) {
		global $jump;
		$p=array('','','','',''); 
		$ix=0;
		$p[$ix]='';
		$lastpos=$this->pos; //save cursor position
		$tok=$this->getToken();
		
		if ($tok==P_BEGIN) {
			$tok=$this->getToken();
			$stack=1;
			while ($stack > 0) {
				if ($tok==P_BEGIN) $stack++;
				else if ($tok==P_END) $stack--;
				if ($stack==0) break;	
				
				$p[$ix] .= $tok;
				$tok=$this->getToken();
			}
			$lastpos=$this->pos; //save cursor position
			$tok=$this->getToken();
		}

		
		do {
			$ix++;			
			if ($tok== B_BEGIN ) {
			    // do not parse inside this commands
				if ($cmd=='\c' || $cmd=='\php' || $cmd=='\script' || $cmd=='\کد' 
					|| $cmd=='\\asis' || $cmd=='\\کاتی'
					|| strpos($cmd,'\style')===0 || strpos($cmd,'\استایل')===0) {
					 // do not parse commands inside { }
					 $p[$ix]='';
					 $sp=1;
					 while ($sp>0) {
					 	if ($this->input[$this->pos]=='{') $sp++;
					 	else
					 	if ($this->input[$this->pos]=='}') $sp--;
					 	if ($sp>0) 
					 		$p[$ix].=$this->input[$this->pos];
					 	$this->pos++;
					 }
				} else {
					$p[$ix]='';
					$tok=$this->getToken();

					while ($tok != B_END && $tok!=EOF) {
						if (strlen($tok)>1 && substr($tok,0,1)=='\\') { 
							$p[$ix] .= $this->parse($tok);
						} else {
							if (strlen($tok)>0 && $tok[0]=='\\') {
								$tok=substr($tok,1,strlen($tok)-1);
							}
							$p[$ix] .= $tok;
						}
						$tok=$this->getToken();
					}
				}
			} else {
				$this->pos = $lastpos;
				break;
			}
			$lastpos=$this->pos; //save cursor position
			$tok=$this->getToken();
		} while(1);

		if ($cmd=='\#') {
			$result= constant( $p[1] );
		} else if ($cmd=='\define') { //define constant
			define( "$p[1]" , "$p[2]" );
			$result=''; 
		} 
		else if (strpos($cmd,"\\{")===0) {
			$result='{';
		} else		
		if (strpos($cmd,"\\}")===0) {
			$result='}';
		} else				
		if (strpos($cmd,"\\style\\")===0) {
			$result=$jump[FEX_STYLE]->run($cmd, $p);
		} else 
		if (strpos($cmd,"\\دستور")===0) {
			$cmd=str_replace('\دستور', "", $cmd);
			$jump['\newcommand']->run($cmd, $p);
			$result='';
		} else
		if (strpos($cmd,'\newcommand')===0) {
			$cmd=str_replace('\newcommand', "", $cmd);
			$jump['\newcommand']->run($cmd, $p);
			$result='';
		} else	{
			// \command.class#id~name 
			$split=splitCommand($cmd);
			$cmd=$split[0];
			$varName='';
			if (count($split)>1) {
				$class=''; $id=''; $name='';
				$space='';
				for ($i=1; $i<count($split); $i++) {
					if ($split[$i][0]=='.') { //class name
					    $temp_class=substr($split[$i],1);
					    if (isset($jump['\\'. $temp_class])) {					        					        if ($cmd!="\\c") {
					            $temp_class=$jump['\\'. $temp_class]->run($cmd,$p);
					        }
					    }
						$class .= $space . $temp_class;
						$space=' ';
					}
					else if ($split[$i][0]=='#') $id = substr($split[$i],1);
					else if ($split[$i][0]=='~') $name = substr($split[$i],1);
					else if ($split[$i][0]=='$') $varName =  $split[$i];
				}
				if (!isset($p[0])) $p[0]='';
				if ($class!='') $p[0] .= " class='$class' ";
				if ($id!='') $p[0] .= " id='$id' ";
				if ($name!='') $p[0] .= " name='$name' ";
			}

			if ($cmd==="\\\\") $result= "\\";
			else if (isset($jump[$cmd])) {
				$jump[$cmd]->setVarName($varName);
				$result=$jump[$cmd]->run($cmd, $p); 
			} else	 error("$cmd را نمی‌شناسم.");
		}

		return $result;
	}
}
?>
