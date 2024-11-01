<?php
/*
Plugin Name: Auto Translator
Plugin URI: http://uberdork.supertwist.net/download/autotranslator.tar.gz
Description: This plugin inserts a flag set to the top of the WordPress sidebar. The base language is pulled from the wp-config file. When a visitor selects a flag, your webpage is automatically translated via one of several on-line automatic language processing tools. No guarantees on the accuracy of the translation!
Author: Thom Skrtich
Version: 0.1b
Author URI: http://uberdork.supertwist.net
*/
/*  Copyright 2005  Thom Skrtich  (email : bisohpthom@supertwist.net)
**
**  This program is free software; you can redistribute it and/or modify
**  it under the terms of the GNU General Public License as published by
**  the Free Software Foundation; either version 2 of the License, or
**  (at your option) any later version.
**
**  This program is distributed in the hope that it will be useful,
**  but WITHOUT ANY WARRANTY; without even the implied warranty of
**  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
**  GNU General Public License for more details.
**
**  You should have received a copy of the GNU General Public License
**  along with this program; if not, write to the Free Software
**  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// include something interesting there

if ( ! class_exists( 'AutoTranslator' ) ) :
class AutoTranslator {

  var $settings = array();
  var $xlate_services = array(
    'xlate_google'     => 'Google Language Tools',
    'xlate_worldlingo' => 'WorldLingo',
    #'xlate_online'     => 'Online Translator',
  );

  function AutoTranslator ()
  {
    if (isset($this))
    {
      /* get our settings */
      $this->settings = get_option('autotranslator');

      /* check and see how we were called */
      if(isset($_GET['mapimage']))
      {
        /* just output the map */
        header("Content-Type: image/png");
        echo $this->get_map();

      } elseif(isset($_GET['page']) && $_GET['page'] == basename(__FILE__)) {
        /* print the admin menu */
        //$this->plugin_options();
      }

      /* offer up the options menu */
      if (is_plugin_page()) {
         AutoTranslator::plugin_options();
      }

      if (!isset($this->settings['baselang'])){
        $this->settings['baselang'] = WPLANG;
        $this->settings['servicename'] = 'xlate_google';
        update_option('autotranslator', $this->settings);
      }

      add_action('admin_head',   array(&$this, 'admin_head'));
      add_action('admin_footer', array(&$this, 'admin_footer'));
      add_action('wp_meta',      array(&$this, 'wp_meta'));

    }

  } //end constructor

  function admin_head()
  {
    if (function_exists('add_options_page')) {
      //add_submenu_page('index.php', 'XTranslator', 'Trans', 8, basename(__FILE__), array(&$this, 'plugin_options'));
      //add_management_page('Translator', 'Translator', 8, basename(__FILE__), array(&$this, 'plugin_options'));
      //add_options_page('AutoTranslator', 'Translator', 8, basename(__FILE__), array(&$this, 'plugin_options'));
      add_options_page('AutoTranslator', 'Translator', 8, basename(__FILE__));
    }
  }

  function admin_footer()
  {
    update_option('autotranslator', $this->settings);
  }

  function plugin_options()
  {
    if( isset( $_POST['update_options'] ) ) {
      $this->settings['baselang'] = trim( $_POST['baselang'] );
      $this->settings['servicename'] = trim( $_POST['servicename'] );
      update_option('autotranslator', $this->settings);
      echo '<div class="updated"><p><strong>' . __('Options updated.', 'spellerdomain') . '</strong></p></div>';
    }

    ?>

    <div class="wrap"> 
    <h2>AutoTranslator Options</h2>
    <p>This plugin does NOT perform any direct language translation.  It merely forwards your reader to any one of the translation services
       listed below along with the URL for the page they are currently viewing.  No guarantees are made regarding the accuracy of the translation.</p>
    
    <p>The translator requires a base language identifier and your choice of web translator service from the provided list.</p>
    <form name="translator" method="post" >
      <input type="hidden" name="action" value="update" />
      <fieldset class="options">
      <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 

      <tr> 
      <th width="33%" valign="top" scope="row">Base Language: </th> 
      <td>
      <input name="baselang" type="text" id="baselang" value="<? echo $this->settings['baselang']; ?>" size="30" /><br />
      <code>wp-config.php<code>: <code><?php echo WPLANG;?></code>	
      </td> 
      </tr> 

      <tr>
      <th scope="row">Translation Service:</th>
      <td><select name="servicename" id="servicename">
      <?php
         $selopts = "";
         foreach($this->xlate_services as $key => $val) {
           $selopts .= "<option value='" . $key . "'";
           if ($key == $this->settings['servicename']) 
              $selopts .= "selected='selected'";
           $selopts .= ">";
           $selopts .= $this->xlate_services[$key] . "</option>";
         }
         echo $selopts;
      ?>
      </select></td>
      </tr>

      </table> 
      </fieldset>
    
      <p class="submit"><input type="submit" name="update_options" value="Update Options &raquo;" /></p>

    </form> 
    </div>
  
    <?php

  }

  function wp_meta()
  {
    switch($this->settings['servicename']) {

      case 'xlate_google':
        $this->xlate_google();
        break;
      case 'xlate_worldlingo':
        $this->xlate_worldlingo();
        break;
      case 'xlate_online':
        $this->xlate_online();
        break;

    }

  }

  function xlate_google()
  {
    $translator = "http://translate.google.com/translate";
    $transurl = $translator . "?u=" . get_settings('siteurl') . "&langpair=". $this->settings['baselang'];
    ?>

    <div id="langtrans">
      <MAP  NAME="mapset">
      <AREA NAME="English"   COORDS="0,0,28,20"    HREF="<?php echo $transurl . "|en";?>">
      <AREA NAME="German"    COORDS="28,0,56,20"   HREF="<?php echo $transurl . "|de";?>">
      <AREA NAME="Spanish"   COORDS="56,0,84,20"   HREF="<?php echo $transurl . "|es";?>">
      <AREA NAME="French"    COORDS="84,0,112,20"  HREF="<?php echo $transurl . "|fr";?>">
      <AREA NAME="Italian"   COORDS="112,0,140,20" HREF="<?php echo $transurl . "|it";?>">
      <AREA NAME="Portugese" COORDS="140,0,170,20" HREF="<?php echo $transurl . "|pt";?>">
      </MAP>
      <IMG USEMAP="#mapset" border=0 ID=idFlags SRC="<?php echo $_SERVER[PHP_SELF];?>?mapimage=flags">
    </div>

    <?php
    $this->jscript();

  }

  function xlate_worldlingo()
  {
    $translator = "http://www.worldlingo.com/wl/translate";
    $transurl = $translator . "?wl_url=" . get_settings('siteurl') . "&wl_srclang=". $this->settings['baselang'];
    ?>

    <div id="langtrans">
      <MAP  NAME="mapset">
      <AREA NAME="English"   COORDS="0,0,28,20"    HREF="<?php echo $transurl . "&wl_trglang=en";?>">
      <AREA NAME="German"    COORDS="28,0,56,20"   HREF="<?php echo $transurl . "&wl_trglang=de";?>">
      <AREA NAME="Spanish"   COORDS="56,0,84,20"   HREF="<?php echo $transurl . "&wl_trglang=es";?>">
      <AREA NAME="French"    COORDS="84,0,112,20"  HREF="<?php echo $transurl . "&wl_trglang=fr";?>">
      <AREA NAME="Italian"   COORDS="112,0,140,20" HREF="<?php echo $transurl . "&wl_trglang=it";?>">
      <AREA NAME="Portugese" COORDS="140,0,170,20" HREF="<?php echo $transurl . "&wl_trglang=pt";?>">
      </MAP>
      <IMG USEMAP="#mapset" border=0 ID=idFlags SRC="<?php echo $_SERVER[PHP_SELF];?>?mapimage=flags">
    </div>

    <?php
    $this->jscript();

  }

  function xlate_online()
  {
    //http://www.online-translator.com/url/tran_url.asp?lang=en&url=http:\\uberdork.supertwist.net&direction=es
    $translator = "http://www.online-translator.com/url/tran_url.asp";
    $transurl = $translator . "?url=" . get_settings('siteurl') . "&lang=" . $this->settings['baselang'] . "&direction=";
    ?>

    <div id="langtrans">
      <MAP  NAME="mapset">
      <AREA NAME="English"   COORDS="0,0,28,20"    HREF="<?php echo $transurl . "en";?>">
      <AREA NAME="German"    COORDS="28,0,56,20"   HREF="<?php echo $transurl . "de";?>">
      <AREA NAME="Spanish"   COORDS="56,0,84,20"   HREF="<?php echo $transurl . "es";?>">
      <AREA NAME="French"    COORDS="84,0,112,20"  HREF="<?php echo $transurl . "fr";?>">
      <AREA NAME="Italian"   COORDS="112,0,140,20" HREF="<?php echo $transurl . "it";?>">
      <AREA NAME="Portugese" COORDS="140,0,170,20" HREF="<?php echo $transurl . "pt";?>">
      </MAP>
      <IMG USEMAP="#mapset" border=0 ID=idFlags SRC="<?php echo $_SERVER[PHP_SELF];?>?mapimage=flags">
    </div>

    <?php
    $this->jscript();

  }

  function jscript() {
    ?>

    <script language="JavaScript" type="text/javascript">
      var sidebar = document.getElementById("sidebar");
      var langtrans = document.getElementById("langtrans");
      var parent = sidebar.parentNode;
      parent.insertBefore(langtrans, sidebar);
      langtrans.setAttribute("id", "sidebar");
    </script>

    <?php

  }

  function get_map() {
  /*
  ** the following contains the base64 encoded background mapset.
  */
  $mapfile = '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';

  return base64_decode($mapfile);
  }

  function include_up($filename)
  {
    $c=0;
    while(!is_file($filename))
    {
      $filename = '../' . $filename;
      $c++;
      if($c==30) {
        echo 'Could not find ' . basename($filename) . '.';
        return '';
      }
    }
    return $filename;
  }

}//end class
endif;

//require_once(AutoTranslator::include_up('wp-config.php'));

$autoTrans = new AutoTranslator();

?>
