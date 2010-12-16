<?php

class About
{
    /**
    * About screen
    **/
    function About()
    {
        $this->about = new GtkWindow;
        $this->about->set_title('Agata Report ' . AGATA_VERSION);
        $this->about->set_policy(false, false, false);
        $this->about->connect_object('key_press_event', array(&$this, 'Close'));
		$this->about->realize();

		$GdkPixmap = Gdk::pixmap_create_from_xpm(
				$this->about->window, null, images . 'about.xpm');

		$Pixmap = new GtkPixmap($GdkPixmap[0], $GdkPixmap[1]);

        $Fixed  = new GtkFixed;
        $Fixed->put($Pixmap, 0, 0);
        $botao  = new GtkButton(_a('Credits'));
        $botao->connect_object('clicked', array(&$this, 'Credits'));
        $Fixed->put($botao, 367, 147);

        $botao  = new GtkButton('ChangeLogs');
        $botao->connect_object('clicked', array(&$this, 'Changelog'));
        $Fixed->put($botao, 356, 174);

		$this->about->set_position(GTK_WIN_POS_CENTER);
		$this->about->add($Fixed);
		$this->about->show_all();
    }

    /**
    * Show the credits window
    */
    function Credits()
    {
        $iwindow = new GtkWindow;
        $iwindow->set_title('Info');
        $iwindow->set_default_size(540, 400);
        $iwindow->set_position(GTK_WIN_POS_CENTER);

        $scrolled_win = new GtkScrolledWindow();
        $scrolled_win->set_border_width(5);
        $scrolled_win->set_policy(GTK_POLICY_AUTOMATIC, GTK_POLICY_AUTOMATIC);

        $iwindow->add($scrolled_win);

        $HelpText = new GtkText();
        $scrolled_win->add($HelpText);
        $scrolled_win->show();

        $fd = fopen('CREDITS', "r");
        while (!feof ($fd))
        {
            $buffer = fgets($fd, 500);
            $buffer = ereg_replace("\n", '', $buffer);
            $strings[] = $buffer;
        }
        fclose($fd);
        $font = gdk::font_load ("-bitstream-courier-medium-r-normal-*-*-140-*-*-m-*-iso8859-9");
        foreach ($strings as $string)
        {
            $HelpText->insert($font, null, null, "$string\n");
        }
        $HelpText->show();
        $HelpText->set_usize(364, -1);

        $iwindow->show();
    }

    /**
    * Show the changeLog window
    */
    function Changelog()
    {
        $iwindow = new GtkWindow;
        $iwindow->set_title('Info');
        $iwindow->set_default_size(548, 400);
        $iwindow->set_position(GTK_WIN_POS_CENTER);

        $scrolled_win = new GtkScrolledWindow();
        $scrolled_win->set_border_width(5);
        $scrolled_win->set_policy(GTK_POLICY_AUTOMATIC, GTK_POLICY_AUTOMATIC);

        $iwindow->add($scrolled_win);

        $HelpText = new GtkText();
        $scrolled_win->add($HelpText);
        $scrolled_win->show();

        $fd = fopen('CHANGELOG', "r");
        while (!feof ($fd))
        {
            $buffer = fgets($fd, 500);
            $buffer = ereg_replace("\n", '', $buffer);
            $strings[] = $buffer;
        }
        fclose($fd);
        $font = gdk::font_load ("-bitstream-courier-medium-r-normal-*-*-140-*-*-m-*-iso8859-9");
        foreach ($strings as $string)
        {
            $HelpText->insert($font, null, null, "$string\n");
        }
        $HelpText->show();
        $HelpText->set_usize(364, -1);

        $iwindow->show();
    }

    function Close()
    {
        $this->about->hide();
    }
}
?>