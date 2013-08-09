<?php

echo $this->Form->create('Login', array('class' => 'form-signin'));

    echo $this->Session->flash();

    echo $this->Html->tag('h3', 'Login');

    echo $this->Form->input('user', array('class' => 'input-block-level', 'placeholder' => 'User', 'label' => false));
    echo $this->Form->input('password', array('class' => 'input-block-level', 'placeholder' => 'Password', 'label' => false));

    echo $this->Form->button('Login', array('class' => 'btn btn-primary'));

  echo $this->Form->end();

?>
