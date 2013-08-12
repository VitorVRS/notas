<h3>Selecione a opção</h3>
<ul class="nav nav-tabs nav-stacked">
  <li><?php echo $this->Html->link('Ver Notas', array('controller' => 'main', 'action' => 'getNotas'))?></li>
  <li><?php echo $this->Html->link('Ver Faltas', array('controller' => 'main', 'action' => 'getFaltas'))?></li>
  <li><?php echo $this->Html->link('Quadro de Horários', array('controller' => 'main', 'action' => 'quadroHorarios'))?></li>
</ul>