<?php
$includePath='';
$libPath='';

$jump=array();	$template=array();	$vars=array(); $styles=array();

class fexCommand {
	public $cmd;
	public $template;
	public $secret;
	public $varName;
	
	public function __construct($cmd, $p) {
		$this->cmd=$cmd;
		$this->template= $p;
		$this->secret= "@&$~!";
	}
	
	public function setVarName($n) { $this->varName= $n ; }
	
	public function run($cmd, $p) {
		$result=$this->template[1];
		
		//replace # with @ to prevent parameter replacement
		$result= str_replace("#", $this->secret, $result);
		for($i=0; $i<count($p); $i++) {
			if (isset($p[$i]))
				$result= str_replace("$this->secret$i", $p[$i], $result);
			else break;
		}
		$result= str_replace("$this->secret", '#', $result);
		return $result;	
	} 
}

class fexNewCommand extends fexCommand {
	public function run($cmd, $p) {
		global $jump;//, $template;
		$jump[$cmd]=new fexCommand($cmd, $p);
	}
};

class fexCallCommand extends fexCommand {
	public function run($cmd, $p) {
		if (!file_exists($p[1])) {
			return $p[1];
		} else {
			$inc=file_get_contents($p[1]);
			$f=new fex($inc);
			return $f->parse(FEX_DOC);
		}
	}
}

class fexStyleCommand extends fexCommand {
	public function run($cmd, $p) {
	  global $styles;
	  $res='';
	  $sdata=  str_replace('\\style\\','',$cmd);
	  
	  $file= __DIR__ . "/../www/css/" . $sdata . ".css";
	  $url = $GLOBALS['APP_PATH'] . "/css/" . $sdata . ".css";
	  
	  if (trim($p[1])!='') {
		  if (!isset($styles[$file])) { //create new css file  	
		  	file_put_contents($file, $p[1]);
		  	$styles[$file]=1;
		  } else { //append css to file
		  	file_put_contents($file, $p[1], FILE_APPEND);
		  	$styles[$file]++;
		  }
	  }

	  //generate css output in first call
	  if ($p[0]=='*')
		  $res="<link rel=\"stylesheet\" href=\"$url\" type=\"text/css\" />";
	  return $res;
	}
};

class fexDocCommand extends fexCommand {
	public  function run ($cmd, $p) {
		$p[1]= str_replace("style=''",'',$p[1]);
		return $p[1];
	}
};


class fexAsIsCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;
		$r.= $p[1];
		return $r;	
	}
}

class fexRepeatCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;

		$r="<?php for($this->varName=1; $this->varName <= ($p[0]) ; $this->varName++) { ?>";
		$r.= $p[1];
		$r .= "<?php } ?>";

		return $r;	
	}
}

class fexForCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;
		$c = explode('..', $p[0]);
		$r ="<?php for($this->varName=$c[0]; $this->varName <= ($c[1]) ; $this->varName++) { ?>";
		$r.= $p[1];
		$r .= "<?php } ?>";

		return $r;	
	}
}

class fexWhileCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;
		$r = <<<ENDL
<?php 
if ($p[1]) { 
	do { 	
	 	?>$p[2]<?php	 	
	} while($p[1]); 
} else { ?> 
	$p[3] 
<?php } ?>
ENDL;
		return $r;	
	}
}

class fexForeachCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;
		$c= explode(',', $p[0]);
		$r='';
		$px='';

		for($i=0; $i < count($c); $i++) {
			$px=str_replace("$this->varName",$c[$i], $p[1]);
			$r .= $px;
		}
		return $r;	
	}
}

class fexIfCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;

		$r="<?php if($p[1]) { ?>";
		$r.= $p[2];
		$r .= "<?php } else { ?>";
		$r.= $p[3];
		$r .= "<?php } ?>";		

		return $r;	
	}
};

class fexSwitchCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;

		$r ="<?php switch($p[1]) { ?>";
		$r .= $p[2];
	    $r .= " <?php } ?>"; 
		return $r;	
	}
};


class fexCaseCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;

		$r ="<?php case ($p[1]): ?>";
		$r .= $p[2];
	    $r .= "<?php break; ?>"; 
		return $r;	
	}
};


class fexDefaultCommand extends fexCommand {
	public function run($cmd, $p) {
		global $vars;

		$r ="<?php default: ?>";
		$r .= $p[1];
	    $r .= "<?php break; ?>"; 
		return $r;	
	}
};


?>
