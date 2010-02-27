<?php

require_once 'kelpie_init.php';

class HelloWorld extends Kelpie_App
{
	/**
	 * @get /
	 */
	public function home()
	{
		return 'welcome';
	}

	/**
	 * @get /posts/#id
	 */
	public function view($id)
	{
		return '<a href="/posts/'.$id.'/edit">Edit</a>';
	}

	/**
	 * @get /posts/#id/:action
	 */
	public function edit($id, $action)
	{
		return '<form action="" method="post"><input type="submit" name="test" value="blargh" /></form>';
	}

	/**
	 * @post /posts/#id/:action
	 */
	public function postedit($id, $action)
	{
		return 'Cool! '.$id.'-> '.$action;
	}
}

$server = new Kelpie_Server('0.0.0.0', 8000);
$server->start(new HelloWorld());
