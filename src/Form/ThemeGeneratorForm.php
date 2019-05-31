<?php

namespace Drupal\dropsolid_rocketship_profile\Form;

use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Drupal\Console\Core\Utils\DrupalFinder;

/**
 * Defines form for selecting extra components for the assembler to install.
 */
class ThemeGeneratorForm extends FormBase {

//  const MODULE_PACKAGE_NAME = 'Rocketship';
//
//  const THEME_PACKAGE_NAME = 'Dropsolid Theme';

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $root;

  /**
   * The info parser service.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Assembler Form constructor.
   *
   * @param string $root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Module Handler service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   The Theme Handler service.
   */
  public function __construct($root, InfoParserInterface $info_parser, TranslationInterface $translator, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $themeHandler) {
    $this->root = $root;
    $this->infoParser = $info_parser;
    $this->stringTranslation = $translator;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $themeHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('app.root'),
        $container->get('info_parser'),
        $container->get('string_translation'),
        $container->get('module_handler'),
        $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dropsolid_rocketship_profile_generate_theme';
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   Extra compoments modules.
   */
  public function buildForm(array $form, FormStateInterface $form_state, array &$install_state = NULL) {
    $form['#title'] = $this->t('Theme generator');
    $form['generate_theme_introduction'] = [
      '#weight' => -1,
      '#prefix' => '<p>',
      '#markup' => $this->t("[Optional] You can choose to generate a Rocketship-compatible, component-based theme to use in your site."),
      '#suffix' => '</p>',
    ];

    $options = array(
      'no' => t('No'),
      'yes' => t('Yes'),
    );

    $form['generate_theme'] = [
      '#type' => 'radios',
      '#title' => t('I would like to generate a theme'),
      '#options' => $options,
      '#default_value' => 'no',
      '#description' => t('Skip the fields below if you choose NOT to generate a theme'),
    ];

    $form['theme_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Make a theme'),
      '#description' => t('Fill in the fields below if you want to generate a theme'),
      '#open' => FALSE,
//      '#tree' => FALSE,
    ];

    $form['theme_wrapper']['name'] = [
      '#type' => 'textfield',
      '#title' => t('Theme name'),
      '#default_value' => 'Rocketship Starter',
      '#description' => t('A name to give this theme. Eg. Rocketship Starter or Rocketship Flex'),
    ];

    $form['theme_wrapper']['machine_name'] = [
      '#type' => 'textfield',
      '#title' => t('Machine name'),
      '#default_value' => 'dropsolid_starter',
      '#description' => t('no dashes or spaces, use underscores if needed'),
    ];

    $form['theme_wrapper']['theme_preset'] = [
      '#type' => 'textfield',
      '#title' => t('Theme preset'),
      '#default_value' => 'starter',
      '#description' => t('The preset your theme will be based on. Options are "minimal" [still under development] (structural CSS only), "starter (more styling to get you started with Rocketship elements)" or "flex (default styling for all Rocketship elements)"'),
    ];

    $form['theme_wrapper']['author'] = [
      '#type' => 'textfield',
      '#title' => t('Author'),
      '#default_value' => 'me',
      '#description' => t('Name to put in the package json file'),
    ];

    $form['theme_wrapper']['description'] = [
      '#type' => 'textfield',
      '#title' => t('Author'),
      '#default_value' => 'Generated component-based Drupal 8 theme for use with Dropsolid Rocketship install profile, modules and other components.',
      '#description' => t('Name to put in the package json file'),
    ];

    $form['theme_wrapper']['theme_path'] = [
      '#type' => 'textfield',
      '#title' => t('Theme path'),
      '#default_value' => 'themes/custom',
      '#description' => t('Location to put the theme in. Relative to docroot.'),
    ];

    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#button_type' => 'primary',
      ],
      '#type' => 'actions',
      '#weight' => 5,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // ** run Drupal Console command with the form values, to generate the theme

    $values = $form_state->cleanValues()->getValues();

    $theme_generation = $form_state->getValue('generate_theme');

    if ($theme_generation === 'yes') {

      $theme_name = $values['name'];
      $theme_machine_name = $values['machine_name'];
      $theme_preset = $values['theme_preset'];
      $theme_path = $values['theme_path'];
      $author = $values['author'];
      $description = $values['description'];

      // replace any dashes or spaces in machine name
      $theme_machine_name = str_replace(' ', '_', $theme_machine_name);
      $theme_machine_name = str_replace('-', '_', $theme_machine_name);

      $root = \Drupal::root();

      var_dump('$root:' . "\n");
      var_dump($root . "\n");
//      var_dump('getcwd:' . "\n");
//      var_dump(getcwd() . "\n");

      $drupalFinder = new DrupalFinder();
      $drupalFinder->locateRoot(getcwd());

      var_dump('locateRoot:' . "\n");
      var_dump($drupalFinder->locateRoot(getcwd()) . "\n");

      $drupalRoot = $drupalFinder->getDrupalRoot();
      var_dump('$drupalRoot:' . "\n");
      var_dump($drupalRoot . "\n");

      $command = $root . '/../vendor/drupal/console/bin/drupal generate:rocketshiptheme --theme="' . $theme_name . '" --machine-name=' . $theme_machine_name . ' --theme-preset=' . $theme_preset . ' --author="'. $author . '" --description="' . $description . '" --theme-path=' . $theme_path . ' --root=' . $root .  ' -y';

      var_dump('command:' . "\n");
      var_dump($command . "\n");

      // TO DO: run command, trigger a progress bar until this is finished

      $process = new Process($command);
      $process->run();

      // executes after the command finishes
      if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
      }

      var_dump($process->getOutput() . "\n");
      echo $process->getOutput();


      sleep(30);

      $GLOBALS['install_state']['dropsolid_rocketship_profile']['generate_theme'] = TRUE;




//      $pipes = array();
//      $descriptors = array(
//        0 => array("pipe", "r"),
//        1 => array("pipe", "w"),
//        2 => array("pipe", "w"),
//      );
//
//      $process = proc_open($command, $descriptors, $pipes) or die("Can't open process $command!");
//
//      $output = "";
//      while (!feof($pipes[2])) {
//        $read = array($pipes[2]);
//        stream_select($read, $write = NULL, $except = NULL, 0);
//        if (!empty($read)) {
//          $output .= fgets($pipes[2]);
//        }
//        # HERE PARSE $output TO UPDATE DOWNLOAD STATUS...
//        print $output;
//      }
//      fclose($pipes[0]);
//      fclose($pipes[1]);
//      fclose($pipes[2]);
//      proc_close($process);

    } else {
      $GLOBALS['install_state']['dropsolid_rocketship_profile']['generate_theme'] = FALSE;
    }

  }

  /**
   * Inserts a key/value pair into an array before given index.
   *
   * @param array $array
   *   The array to work on.
   * @param string $key
   *   The key to insert.
   * @param mixed $value
   *   The value to insert.
   * @param int $index
   *   The index to insert before.
   *
   * @return array
   *   The original array with the new value added before the index.
   */
//  protected function insertBeforeIndex(array $array, $key, $value, $index) {
//    $array = array_slice($array, 0, $index, TRUE) +
//        [$key => $value] +
//        array_slice($array, $index, count($array) - $index, TRUE);
//
//    return $array;
//  }

}
