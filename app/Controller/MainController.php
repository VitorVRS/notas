<?php

class MainController extends AppController {

  public $name = 'Main';

  public $layout = 'main';

  public function index() {
    $this->Session->delete('Notas');
    $this->redirect('login');
  }

  public function message() {

  }

  public function login() {
    $cookies = $this->Session->read('Notas.Cookies');
    if (!empty($cookies)) {
        $this->setContextsView();
        return $this->render('get_contexts');
    }

    if (!empty($this->request->data['Login']['user'])) {
        $user = $this->decodeStr($this->request->data['Login']['user']);
        $pass = $this->decodeStr($this->request->data['Login']['password']);

        $this->getContexts($user, $pass);

        $this->setContextsView();

        return $this->render('get_contexts');
    } else {
        $this->Session->delete('Notas');
    }
  }

  private function setContextsView() {
    $nContexts = array();
    $radios = $this->Session->read('Notas.Contextos');
    foreach ($radios as $context) {
        $x = explode('¶', $context['rdContexto']);
        $nContexts[] = $x[3];
    }   

    $this->set('contexts', $nContexts);
  }

  public function menu($key) {
    $contextos = $this->Session->read('Notas.Contextos');
    $contexto = $contextos[$key];

    $this->selectContext($contexto['rdContexto']);

    //$this->redirect('getNotas');
  }

  public $LOGIN_URL = 'https://portal.asav.org.br/Corpore.Net/Login.aspx';
  public $CONTEXT_URL = 'https://portal.asav.org.br/Corpore.Net/Source/Edu-Educacional/RM.EDU.CONTEXTO/EduSelecionarContextoModalWebForm.aspx';
  public $NOTAS_URL = 'https://portal.asav.org.br/Corpore.Net/Main.aspx?SelectedMenuIDKey=mnNotasAval&ActionID=EduNotaAvaliacaoActionWeb';
  public $NOTAS_FALTA_URL = 'https://portal.asav.org.br/Corpore.Net/Main.aspx?SelectedMenuIDKey=mnNotasEtapa&ActionID=EduNotaEtapaActionWeb';
  public $QUADRO_HORARIOS_URL = 'https://portal.asav.org.br/Corpore.Net/Main.aspx?SelectedMenuIDKey=mnQuadroHorario&ActionID=EduQuadroHorarioAlunoActionWeb';

  public function getContexts($user, $pass) {

    App::uses('SimpleHtmlDomBakedComponent', 'Controller/Component');

    $html = new SimpleHtmlDomBakedComponent();
    
    $h =& $this->HttpRequest;

    $h->setUri($this->LOGIN_URL);

    $r = $h->get();

    $html->load($r);

    $inputs = array();
    foreach ($html->find('input') as $input) {
      $inputs[$input->name] = $input->value;
    }

    $inputs['txtUser'] = $user;
    $inputs['txtPass'] = $pass;
    $inputs['ddlAlias'] = 'CorporeRM';

    $h->setData($inputs);

    //Loga
    $pBody = $h->post();

    
    $html->load($pBody);
    $divLogin = $html->find('table[id=tableContainerError]',0);
    $body = $html->find('body[id=bodyError]',0);
    if ($divLogin || $body) {
        $this->Session->setFlash('Dados incorretos', 'default', array('class' => 'message error'));
        $this->Session->delete('Notas.Cookies');
        if ($this->request->is('ajax')) {
            die('dead');
        }
        $this->redirect('login');
    }

    //Pega os cookies
    preg_match_all('/Set-Cookie: (.*)\b/', $pBody, $cookies);
    
    $cookies = $cookies[1];

    $this->Session->write('Notas.Cookies', $cookies);
    $h->setCookies($cookies);

    /* Isto aqui é necessário, pois algumas
    *  pessoas não estavam conseguindo acessar */
    $info = $h->getInfo();
    $nextUrl = $info['redirect_url'];
    $h->setUri($nextUrl);
    $contextoE = $h->get();
    /* /\ Provável futura remoção disto /\ */

    $h->setUri($this->CONTEXT_URL);
    $contextoE = $h->get();

    $html->load($contextoE);
    $radios = array();
    foreach ($html->find('input[type=radio]') as $radio) {
      $radios[][$radio->name] = $radio->value;
    }

    //Dar um jeito de salvar na sessão
    $inputs = array();
    foreach ($html->find('input[type=hidden]') as $input) {
      $inputs[$input->name] = $input->value;
    }

    $this->Session->write('Notas.Data', $inputs);

    $this->Session->write('Notas.Contextos', $radios);

  }

  private function selectContext($context) {
    App::uses('SimpleHtmlDomBakedComponent', 'Controller/Component');

    $html = new SimpleHtmlDomBakedComponent();
    
    $h =& $this->HttpRequest;

    $inputs = $this->Session->read('Notas.Data');

    $inputs['rdContexto'] = $context;

    #COOKIES ANTIGOS AQUI
    #DAR UM JEITO DE PEGAR ELES

    $h->setUri($this->CONTEXT_URL);
    $h->setData($inputs);
    
    $cookies = $this->Session->read('Notas.Cookies');
    $h->setCookies($cookies);

    $pContextBody = $h->post();


    #PEGA OS NOVOS COOKIES
    preg_match_all('/Set-Cookie: (.*)\b/', $pContextBody, $cookies2);
    foreach ($cookies2[1] as $cookie) {
      $cookies[] = $cookie;
    }

    #DAR UM JEITO DE SALVAR OS COOKIES EM ALGUM LUGAR

    $this->Session->write('Notas.Cookies', $cookies);
    $h->setCookies($cookies);

    return;
  }

  public function getNotas() {
    $cookies = $this->Session->read('Notas.Cookies');
    
    if (empty($cookies)) {
        $this->Session->setFlash('Sua sessão expirou.');
        if ($this->request->is('ajax')) {
            die('dead');
        }
        $this->redirect('login');
    }

    App::uses('SimpleHtmlDomBakedComponent', 'Controller/Component');

    $html = new SimpleHtmlDomBakedComponent();
    
    $h =& $this->HttpRequest;
    
    $h->setUri($this->NOTAS_URL);
    
    $cookies = $this->Session->read('Notas.Cookies');
    $h->setCookies($cookies);
    
    $gNotas = $h->get();
    $html->load($gNotas);

    if (strpos($gNotas, '/Corpore.Net/Login.aspx') !== false) {
        $this->Session->setFlash('Sua sessão expirou.');
        $this->Session->delete('Notas.Cookies');
        $this->redirect('index');
    };

    $divPrincipal = $html->find('div[id=ctl23_pnlPrincipal]',0);

    $etapas = array();
    foreach ($divPrincipal->find('span[id^=ctl23_GrupoEtapa_PanelEtapa]') as $k=>$etapa) {

        $etapas[$k] = array(
            'nome' => $etapa->title
        );
    }

    foreach ($divPrincipal->find('div[id^=ctl23_PanelEtapa]') as $k=>$etapa) {
        foreach ($etapa->children() as $key => $child) {
            if ($child->tag == 'span') {
                $etapas[$k]['Materia'][$key]['nome'] = $child->children(0)->plaintext;

                $table = $etapa->children($key+1);


                foreach ($table->find('tr') as $ktr => $tr) {
                    if ($ktr == 0) {
                        continue;
                    }

                    if ($tr->align == 'right') {
                        $pTObj = $tr->children(2)->find('text',1);
                        $nTObj = $tr->children(3)->find('text',1);
                        $etapas[$k]['Materia'][$key]['pesoTotal'] = $pTObj ? $pTObj->plaintext : '';
                        $etapas[$k]['Materia'][$key]['notaTotal'] = $nTObj ? $nTObj->plaintext : '';
                        continue;
                    }

                    $etapas[$k]['Materia'][$key]['Avaliacao'][$ktr] = array(
                        'nome' => $tr->children(0)->plaintext,
                        'peso' => $tr->children(2)->plaintext,
                        'nota' => $tr->children(3)->plaintext
                    );
                }

            }
        }
    }
    $this->set('etapas', $etapas);

  }

  public function getFaltas() {

    $cookies = $this->Session->read('Notas.Cookies');
    
    if (empty($cookies)) {
        $this->Session->setFlash('Sua sessão expirou.');
        if ($this->request->is('ajax')) {
            die('dead');
        }
        $this->redirect('login');
    }

    App::uses('SimpleHtmlDomBakedComponent', 'Controller/Component');

    $html = new SimpleHtmlDomBakedComponent();
    
    $h =& $this->HttpRequest;
    
    $h->setUri($this->NOTAS_FALTA_URL);
    
    $cookies = $this->Session->read('Notas.Cookies');
    $h->setCookies($cookies);
    
    $gFaltas = $h->get();
    $html->load($gFaltas);

    if (strpos($gFaltas, '/Corpore.Net/Login.aspx') !== false) {
        $this->Session->setFlash('Sua sessão expirou.');
        $this->Session->delete('Notas.Cookies');
        $this->redirect('index');
    };

    $table = $html->find('table[id=ctl23_xgvFaltas]',0);

    $faltas = array();
    if ($table) {
        $tableFaltas = $table->find('table[id=ctl23_xgvFaltas_DXMainTable]',0);

        foreach ($tableFaltas->find('tr[id^=ctl23_xgvFaltas_DXDataRow]') as $k => $falta) {
            $n = $k == 0 ? 1 : 0;
            $mat = $falta->children(2)->find('text',$n);
            $quant = $falta->children(4)->find('text',0);
            $faltas[$k] = array(
                'materia' => $mat ? $mat->plaintext : '',
                'quantidade' => $quant ? $quant->plaintext : ''
            );
        }
    }

    $this->set('faltas', $faltas);
  }

  public function quadroHorarios() {

     $cookies = $this->Session->read('Notas.Cookies');
    
    if (empty($cookies)) {
        $this->Session->setFlash('Sua sessão expirou.');
        if ($this->request->is('ajax')) {
            die('dead');
        }
        $this->redirect('login');
    }

    App::uses('SimpleHtmlDomBakedComponent', 'Controller/Component');

    $html = new SimpleHtmlDomBakedComponent();
    
    $h =& $this->HttpRequest;
    
    $h->setUri($this->QUADRO_HORARIOS_URL);
    
    $cookies = $this->Session->read('Notas.Cookies');
    $h->setCookies($cookies);
    
    $gFaltas = $h->get();
    $html->load($gFaltas);

    if (strpos($gFaltas, '/Corpore.Net/Login.aspx') !== false) {
        $this->Session->setFlash('Sua sessão expirou.');
        $this->Session->delete('Notas.Cookies');
        $this->redirect('index');
    };

    $table = $html->find('table[id=ctl23_gvDisciplinas]',0);
    $tableDias = $html->find('div[id=divQuadroHorario]', 0)->find('span', 0)->find('table', 0);
    
    $dias = array(
        'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado', 'Domingo'
    );

    $materias = array();
    if ($tableDias) {
        foreach ($tableDias->find('tr') as $x => $tr) {
            if (!$x) continue;
            foreach ($tr->find('td') as $k => $td) {
                if (!$k) continue;

                $mat = str_replace("&nbsp;", "", htmlspecialchars_decode($td->plaintext));
                if (!$mat) continue;

                if (isset($materias[$td->plaintext]) && !in_array($dias[$k-1], $materias[$mat])) {
                    $materias[$mat][] = $dias[$k-1];
                } else{
                    $materias[$mat] = array($dias[$k-1]);
                }
            }
        }
    }

    $horarios = array();

    if ($table) {
        $tableHorarios = $table->find('tr');

        foreach ($tableHorarios as $k => $tr) {
            if ($k) {

                # Não me orgulho disso
                # É sábado e to com preguiça de validar.
                $prof = @explode('</b>', explode('<b>', htmlspecialchars_decode($tr->find('a',0)->onclick))[1])[0];

                $dias = implode(' / ', $materias[(html_entity_decode($tr->children(2)->plaintext))]);

                $horarios[] = array(
                    'codigo' => $tr->children(1)->plaintext,
                    'disciplina' => $tr->children(2)->plaintext,
                    'sala' => $tr->children(8)->plaintext,
                    'professor' => $prof,
                    'dia' => $dias
                );

            }
        }
    }

    $this->set('horarios', $horarios);

  }

    private function decodeStr($coded) {
        return $coded;
        $key = "SXGWLZPDOKFIVUHJYTQBNMACERxswgzldpkoifuvjhtybqmncare"; 
        $uncoded = "";
        $chr;
        for ($i = strlen($coded) - 1; $i >= 0; $i--) {
           $chr = $coded{$i};
            $uncoded .= ($chr >= "a" and $chr <= "z" or $chr >= "A" and $chr <= "Z") ?
            chr(65 + strpos($key, $chr) % 26) :
            $chr; 
        }
        return $uncoded;   
    }
}

?>