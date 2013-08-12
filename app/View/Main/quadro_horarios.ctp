<?php
  
  if (empty($horarios)) :
    echo $this->Html->tag('h3', 'Nenhum horário.');
  else :
    echo $this->Html->tag('h3', 'Quadro de Horários');

  ?>
    <table class="table table-bordered table-striped table-condensed">
      <thead>
        <tr>
          <th>Matéria</th>
          <th>Sala</th>
          <th>Professor</th>
          <th>Dia</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($horarios as $horario) : ?>        
          <tr>
            <td><?php echo $horario['disciplina']; ?></td>
            <td><?php echo $horario['sala']; ?></td>
            <td><?php echo $horario['professor']; ?></td>
            <td><?php echo $horario['dia']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php

  endif;

?>

<?php echo $this->Html->link('Trocar Contexto', 'login', array('class' => 'btn btn-small')); ?>