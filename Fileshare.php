<?php
/* Used to bust the cache and to display footer version number */
$version = '1337';

$config = array(
    /**
     * Authentication options
     */
    'authentication' => false,
    /**
     * Enables single-page features
     */
    'single_page' => false,
    /**
     * Formatting options
     */
    'format' => array(
        'title' => 'Index of %s', /* Title format where %s is the current path */
        'date' => array('d/m/y H:i', 'd/m/y'), /* Date formats (desktop, mobile) */
        'sizes' => array(' B', ' KiB', ' MiB', ' GiB', ' TiB') /* Size formats */
    ),
    /**
     * Favicon options
     */
    'icon' => array(
        'path' => '/favicon.ico', /* What favicon to use */
        'mime' => 'image/x-icon' /* Favicon mime type */
    ),
    /**
     * Sorting options.
     * 
     * Used as default until the client sets their own sorting settings
     */
    'sorting' => array(
        'enabled' => false, /* Whether the server should sort the items */
        'order' => SORT_ASC, /* Sorting order. asc or desc */
        'types' => 0, /* What item types to sort. 0 = both. 1 = files only. 2 = directories only */
        'sort_by' => 'name', /* What to sort by. available options are name, modified, type and size */
        'use_mbstring' => false /* Enabled mbstring when sorting */
    ),
    /**
     * Gallery options
     */
    'gallery' => array(
        'enabled' => true, /* Whether the gallery plugin should be enabled */
        'reverse_options' => false, /* Reverse search options for images (when hovering over them) */
        'scroll_interval' => 50, /* Break in ms between scroll navigation events */
        'list_alignment' => 0, /* List alignment where 0 is right and 1 is left */
        'fit_content' => true, /* Whether the media should be forced to fill the screen space */
        'image_sharpen' => false, /* Attempts to disable browser blurriness on images */
    ),
    /**
     * Preview options
     */
    'preview' => array(
        'enabled' => true, /* Whether the preview plugin should be enabled */
        'hover_delay' => 75, /* Delay in milliseconds before the preview is shown */
        'cursor_indicator' => true /* Displays a loading cursor while the preview is loading */
    ),
    /**
     * Extension that should be marked as media.
     * These extensions will have potential previews and will be included in the gallery
     */
    'extensions' => array(
        'image' => array('jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'bmp', 'webp'),
        'video' => array('webm', 'mp4', 'ogg', 'ogv', 'mov')
    ),
    /**
     * Injection options
     */
    'inject' => false,
    /**
     * Styling options
     */
    'style' => array(
        /* Set to a path relative to the root directory (location of this file) containg .css files.
         * Each .css file will be treated as a separate theme. Set to false to disable themes */
        'themes' => array(
          'path' => false,
          'default' => false
        ),
         /* Cascading style sheets options */
        'css' => array(
          'additional' => false
        ),
        /* Enables a more compact styling of the page */
        'compact' => false
    ),
    /**
     * Filter what files or directories to show.

     * Uses regular expressions. All names *matching* the regex will be shown.
     * Setting the value to false will disable the respective filter
     */
    'filter' => array(
        'file' => false,
        'directory' => false
    ),
    /**
     * Calculates the size of directories.

     * This can be intensive, especially with the recursive
     * option, so be aware of that
     */
    'directory_sizes' => array(
      /* Whether directory sizes should be calculated or not */
      'enabled' => false,
      /* Recursively scans the directories when calculating the size */
      'recursive' => false
    ),
    /* Processing functions */
    'processor' => false,
    /* Should ? and # characters be encoded when processing URLs */
    'encode_all' => false,
    /* Whether this .php file should be directly accessible */
    'allow_direct_access' => false,
    /* Set to 'strict' or 'weak'.
     * 'strict' uses realpath() to avoid backwards directory traversal
     * whereas 'weak' uses a similar string-based approach */
    'path_checking' => 'strict',
    /* Enabled the performance mode */
    'performance' => false,
    /* Whether extra information in the footer should be generated */
    'footer' => array(
      'enabled' => true,
      'show_server_name' => true
    ),
    /**
     * Displays a simple link to the git repository in the
     * footer along with the current version.
     * 
     * I would really appreciate it if you would keep this enabled
     */
    'credits' => true,
    /**
     * Enables console output in JS and PHP debugging.
     * Also enables random query-strings for js/css files to bust the cache
     */
    'debug' => true
);

/* Any potential libraries and so on for extra features will appear here */


/* Define current request URI */
define('CURRENT_URI', rawurldecode($_SERVER['REQUEST_URI']));
/* Define default configuration file */
define('CONFIG_FILE', basename(__FILE__, '.php') . '.config.php');
/* Define the base path of the Indexer */
define('BASE_PATH', isset($_SERVER['INDEXER_BASE_PATH'])
  ? $_SERVER['INDEXER_BASE_PATH']
  : dirname(__FILE__));

/**
 * Helper functions for the Indexer
 */ 
class Helpers
{
  /**
   * Checks if a string starts with a string
   *
   * @param string  $haystack  The string to match against
   * @param string  $needle    The string needle
   * 
   * @return Boolean
   */ 
  public static function startsWith($haystack, $needle)
  {
    return $needle === '' || strrpos($haystack, $needle, - strlen($haystack)) !== false;
  }

  /**
   * A realpath alternative that solves links by using
   * a string-based approach instead
   *
   * @param string  $input  A path
   * 
   * @return String
   */ 
  private static function removeDotSegments($input)
  {
    $output = '';

    while($input !== '')
    {
      if(($prefix = substr($input, 0, 3)) == '../'
        || ($prefix = substr($input, 0, 2)) == './')
      {
        $input = substr($input, strlen($prefix));
      } else if(($prefix = substr($input, 0, 3)) == '/./'
        || ($prefix = $input) == '/.')
      {
        $input = '/' . substr($input, strlen($prefix));
      } else if (($prefix = substr($input, 0, 4)) == '/../'
        || ($prefix = $input) == '/..')
      {
        $input = '/' . substr($input, strlen($prefix));
        $output = substr($output, 0, strrpos($output, '/'));
      } else if($input == '.' || $input == '..')
      {
        $input = '';
      } else {
        $pos = strpos($input, '/');
        if($pos === 0) $pos = strpos($input, '/', $pos+1);
        if($pos === false) $pos = strlen($input);
        $output .= substr($input, 0, $pos);
        $input = (string) substr($input, $pos);
      }
    }

    return $output;
  }

  /**
   * Concentrates path components into a merged path
   *
   * @param string  ...$params   Path components
   * 
   * @return String
   */ 
  public static function joinPaths(...$params)
  {
    $paths = array();

    foreach($params as $param)
    {
      if($param !== '')
      {
        $paths[] = $param;
      }
    }

    return preg_replace('#/+#','/', join('/', $paths));
  }

  /**
   * Checks if the passed path is above a base directory
   * 
   * $useRealpath resolves the paths using a string-based method
   * as opposed to calling `realpath()` directly.
   *
   * @param string   $path          The path to check
   * @param string   $base          The base path
   * @param boolean  $useRealpath   Whether to use realpath
   * 
   * @return String
   */ 
  public static function isAboveCurrent($path, $base, $useRealpath = true)
  {
    return self::startsWith($useRealpath
      ? realpath($path)
      : self::removeDotSegments($path), $useRealpath
        ? realpath($base)
        : self::removeDotSegments($base));
  }

  /**
   * Adds a character to both sides of a string
   * 
   * If the string already ends or starts with the given
   * string, it will be ignored.
   *
   * @param string  $string   String to wrap around
   * @param string  $char     Character to prepend and append
   * 
   * @return String
   */ 
  public static function stringWrap($string, $char)
  {
    if($string[0] !== $char)
    {
      $string = ($char . $string);
    }
  
    if(substr($string, -1) !== $char)
    {
      $string = ($string . $char);
    }

    return $string;
  }
}

  /**
   * Authenticaticates a user
   *
   * @param string   $users   An array of users and their password
   * @param string   $realm   Authenication realm
   * 
   * @return Void
   */ 
function authenticate($users, $realm)
{
  function http_digest_parse($text)
  {
    /* Protect against missing data */
    $neededParts = array(
      'nonce' => 1,
      'nc' => 1,
      'cnonce' => 1,
      'qop' => 1,
      'username' => 1,
      'uri' => 1,
      'response' => 1
    );

    $data = array();
    $keys = implode('|', array_keys($neededParts));

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $text, $matches, PREG_SET_ORDER);

    foreach($matches as $m)
    {
      $data[$m[1]] = $m[3] ? $m[3] : $m[4];
      unset($neededParts[$m[1]]);
    }

    return $neededParts ? false : $data;
  }

  /* Create header for when unathorized */
  function createHeader($realm)
  {
    header($_SERVER['SERVER_PROTOCOL'] . '401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');
  }

  /* Deny access if no digest is set */
  if(empty($_SERVER['PHP_AUTH_DIGEST']))
  {
    createHeader($realm);
    die('401 Unauthorized');
  }

  /* Get digest data */
  $data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);

  /* Deny access if data is invalid or username is unset */
  if(!$data || !isset($users[$data['username']]))
  {
    createHeader($realm);
    die('Invalid credentials.');
  }

  $a1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
  $a2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);

  $validResponse = md5($a1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $a2);

  /* Deny access if data can't be verified */
  if($data['response'] != $validResponse)
  {
    createHeader($realm);
    die('Invalid credentials.');
  }
}

/**
 * Extracts themes from a given path
 *
 * @param string   $basePath     The given base path of the script
 * @param string   $themesPath   A themes path relative to the base path
 * 
 * @return Array
 */ 
function getThemes($basePath, $themesPath)
{
  /* Returnable array */
  $themesPool = array();
  /* Create the absolute path of the directory to scan */
  $absDir = rtrim(Helpers::joinPaths($basePath, $themesPath), '/');

  if(is_dir($absDir))
  {
    /** Iterates over the given path */
    foreach(scandir($absDir, SCANDIR_SORT_NONE) as $item)
    {
      /** Current iterated item (folder / file) */
      $itemPath = Helpers::joinPaths($absDir, $item);

      if($item[0] !== '.')
      {
        if(is_dir($itemPath))
        {
          /* The current item is assumed to be a theme directory */
          foreach(preg_grep('/^('.$item.'|index)\.css$/', scandir(
            $itemPath, SCANDIR_SORT_NONE)
          ) as $theme)
          {
            if($theme[0] !== '.')
            {
              $themesPool[strtolower($item)] = array(
                'path' => Helpers::joinPaths($themesPath, $item, $theme)
              ); break;
            }
          }
        } else if(preg_match('~\.css$~', $item))
        {
          /* The current item is a single .CSS file */
          $themesPool[strtolower(basename($item, '.css'))] = array(
            'path' => Helpers::joinPaths($themesPath, $item)
          );
        }
      }
    }

    return $themesPool;
  } else {
    return false;
  }
}

/**
 * Attempts to search for a configuration file.
 * 
 * If it exists, the default values will be overwritten.
 * Any unset values in the file will take the default values.
 */
if(file_exists(CONFIG_FILE))
{
  $config = include(CONFIG_FILE);
} else if(file_exists('.' . CONFIG_FILE)) /* Also check for hidden (.) file */
{
  $config = include('.' . CONFIG_FILE);
}

/* Default configuration values. Used if values from the above config are unset */
$defaults = array('authentication' => false,'single_page' => false,'format' => array('title' => 'Index of %s','date' => array('m/d/y H:i', 'd/m/y'),'sizes' => array(' B', ' KiB', ' MiB', ' GiB', ' TiB')),'icon' => array('path' => '/favicon.png','mime' => 'image/png'),'sorting' => array('enabled' => false,'order' => SORT_ASC,'types' => 0,'sort_by' => 'name','use_mbstring' => false),'gallery' => array('enabled' => true,'reverse_options' => false,'scroll_interval' => 50,'list_alignment' => 0,'fit_content' => true,'image_sharpen' => false),'preview' => array('enabled' => true,'hover_delay' => 75,'cursor_indicator' => true),'extensions' => array('image' => array('jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'bmp', 'webp'),'video' => array('webm', 'mp4', 'ogv', 'ogg', 'mov')),'inject' => false,'style' => array('themes' => array('path' => false,'default' => false),'css' => array('additional' => false),'compact' => false),'filter' => array('file' => false,'directory' => false),'directory_sizes' => array('enabled' => false, 'recursive' => false),'processor' => false,'encode_all' => false,'allow_direct_access' => false,'path_checking' => 'strict','performance' => false,'footer' => array('enabled' => true, 'show_server_name' => true),'credits' => true,'debug' => false);

/**
 * Call authentication function
 */
if(isset($config['authentication']) &&
  $config['authentication'] &&
  is_array($config['authentication']))
{
  /* If `users` key is an array, make way for it and check for restrictions */
  if(isset($config['authentication']['users']) &&
    is_array($config['authentication']['users']))
  {
    $isRestricted = true;

    /* A `restrict` key is set, check if it matches current path */
    if(isset($config['authentication']['restrict']) &&
      is_string($config['authentication']['restrict']))
    {
      /* Check if `restrict` filter matches the current requested URI */
      $isRestricted = preg_match($config['authentication']['restrict'], CURRENT_URI);
    }

    /* Restrict content if `restrict` filter matches successfully or it is unset */
    if($isRestricted)
    {
      authenticate($config['authentication']['users'], 'Restricted content.');
    }
  } else {
    /* Don't use any potential `users` array to authenticate, use main array instead */
    authenticate($config['authentication'], 'Restricted content.');
  }
}

/**
 * Set default configuration values if the config is missing any keys.
 * This does not traverse too deep at all
 */
foreach($defaults as $key => $value)
{
  if(!isset($config[$key]))
  {
    $config[$key] = $defaults[$key];
  } else if(is_array($config[$key]) &&
    is_array($defaults[$key]))
  {
    foreach($defaults[$key] as $k => $v)
    {
      if(!isset($config[$key][$k]))
      {
        $config[$key][$k] = $defaults[$key][$k];
      }
    }
  }
}

/**
 * Set debugging
 */
if($config['debug'] === true)
{
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

/**
 * Set footer data
 */
$footer = array(
  'enabled' => is_array(
    $config['footer'])
      ? ($config['footer']['enabled'] ? true : false)
      : ($config['footer'] ? true : false),
  'show_server_name' => is_array(
    $config['footer'])
      ? $config['footer']['show_server_name']
      : true
);

/**
 * Set start time for page render calculations
 */
if($footer['enabled'])
{
  $render = microtime(true);
}

if($config['style']['themes']['path'])
{
  $config['style']['themes']['path'] = Helpers::stringWrap(
    $config['style']['themes']['path'], '/'
  );
}

if(!is_array($config['format']['date']))
{
  if(is_string($config['format']['date']))
  {
    $config['format']['date'] = array($config['format']['date']);
  } else {
    $config['format']['date'] = array('d/m/y H:i', 'd/m/y');
  }
}

/**
 * Indexer Class
 */ 
class Indexer extends Helpers
{
  public $path;

  private $relative;

  private $pathPrepend;

  private $requested;

  private $types;

  private $allow_direct;

  private $encode_all;

  function __construct($path, $options = array())
  {
    /* Get requested path */
    $requested = rawurldecode(strpos($path, '?') !== false ? explode('?', $path)[0] : $path);

    /* Set relative path */
    if(isset($options['path']['relative'])
      && $options['path']['relative'] !== NULL)
    {
      $this->relative = $options['path']['relative'];
    } else {
      $this->relative = dirname(__FILE__);
    }

    /* Set encode all options */
    $this->encode_all = $options['encode_all'] ? true : false;

    if(isset($options['path']['prepend'])
      && $options['path']['prepend'] !== NULL
      && strlen($options['path']['prepend']) >= 1)
    {
      $this->pathPrepend = ltrim(rtrim($options['path']['prepend'], '/'), '/');
    } else {
      $this->pathPrepend = NULL;
    }

    /* Declare array for optional processing of data */
    $this->processor = array(
      'item' => NULL
    );

    /* Check for passed processing functions */
    if(isset($options['processor']) && is_array($options['processor']))
    {
      if(isset($options['processor']['item']))
      {
        $this->processor['item'] = $options['processor']['item'];
      }
    }

    /* Set remaining options/variables */
    $this->client = isset($options['client']) ? $options['client'] : NULL;
    $this->allow_direct = isset($options['allow_direct_access']) ? $options['allow_direct_access'] : true;
    $this->path = rtrim($this->joinPaths($this->relative, $requested), '/');
    $this->timestamp = time();
    $this->directory_sizes = $options['directory_sizes'];

    /* Is requested path a directory? */
    if(is_dir($this->path))
    {
      /* Check if the directory is above the base directory (or same level) */
      if(self::isAboveCurrent($this->path, $this->relative))
      {
        $this->requested = $requested;
      } else {
        /* Directory is below the base directory */
        if($options['path_checking'] === 'strict' || $options['path_checking'] !== 'weak')
        {
          throw new Exception("requested path (is_dir) is below the public working directory. (mode: {$options['path_checking']})", 1);
        } else if($options['path_checking'] === 'weak')
        {
          /* If path checking is 'weak' do another test using a 'realpath' alternative instead (string-based approach which doesn't solve links) */
          if(self::isAboveCurrent($this->path, $this->relative, false) || is_link($this->path))
          {
            $this->requested = $requested;
          } else {
            /* Even the 'weak' check failed, throw an exception */
            throw new Exception("requested path (is_dir) is below the public working directory. (mode: {$options['path_checking']})", 2);
          }
        }
      }
    } else {
      /* Is requested path a file (this can only be the indexer as we don't have control over any other files)? */
      if(is_file($this->path))
      {
        /* If direct access is disabled, deny access */
        if($this->allow_direct === false)
        {
          http_response_code(403); die('Forbidden');
        } else {
          /* If direct access is allowed, show current directory of script (if it is above base directory) */
          $this->path = dirname($this->path);

          if(self::isAboveCurrent($this->path, $this->relative))
          {
            $this->requested = dirname($requested);
          } else {
            throw new Exception('requested path (is_file) is below the public working directory.', 3);
          }
        }
      } else {
        /* If requested path is neither a file nor a directory */
        throw new Exception('invalid path. path does not exist.', 4);
      }
    }

    /* Set extension variables */
    if(isset($options['extensions']))
    {
        $this->types = array();

        foreach($options['extensions'] as $type => $value)
        {
          foreach($options['extensions'][$type] as $extension) $this->types[strtolower($extension)] = $type;
        }
    } else {
        $this->types = array(
          'jpg' => 'image',
          'jpeg' => 'image',
          'gif' => 'image',
          'png' => 'image',
          'ico' => 'image',
          'svg' => 'image',
          'bmp' => 'image',
          'webp' => 'image',
          'webm' => 'video',
          'mp4' => 'video',
          'ogg' => 'video',
          'ogv' => 'video'
        );
    }

    /* Set filter variables */
    if(isset($options['filter']) && is_array($options['filter']))
    {
      $this->filter = $options['filter'];
    } else {
      $this->filter = array(
        'file' => false,
        'directory' =>  false
      );
    }

    /* Set size format variables */
    if(isset($options['format']['sizes']) && $options['format']['sizes'] !== NULL)
    {
      $this->format['sizes'] = $options['format']['sizes'];
    } else {
      $this->format['sizes'] = array(' B', ' KiB', ' MiB', ' GiB', ' TiB', ' PB', ' EB', ' ZB', ' YB');
    }

    $this->format['date'] = $options['format']['date'];
  }

  /**
   * andles pathing by taking any potential prepending into mind
   *
   * @param string    $path    A path
   * @param boolean   $isDir   Whether the path should be treated as a directory
   * 
   * @return String
   */ 
  private function handlePathing($path, $isDir = true)
  {
    $path = ltrim(rtrim($path, '/'), '/');

    if($this->pathPrepend)
    {
      if(!empty($path))
      {
        $path = sprintf(
          '/%s/%s%s',
          $this->pathPrepend,
          $path,
          $isDir ? '/' : ''
        );
      } else {
        $path = '/' . $this->pathPrepend . '/';
      }
    } else {
      $path = ('/' . $path . (!empty($path) && $isDir ? '/' : ''));
    }

    return $path;
  }

  /* Gets file/directory information and constructs the HTML of the table */
  public function buildTable($sorting = false, $sort_items = 0, $sort_type = 'modified', $use_mb = false)
  {
    /* Get client timezone offset */

    $cookies = array(
      'timezoneOffset' => intval(is_array($this->client) ? (isset($this->client['timezoneOffset']) ? $this->client['timezoneOffset'] : 0) : 0)
    );

    $timezone = array(
      'offset' => $cookies['timezoneOffset'] > 0 ? -$cookies['timezoneOffset'] * 60 : abs($cookies['timezoneOffset']) * 60
    );

    /* Gets the filename of this .php file. Used to hide it from the folder */
    $script_name = basename(__FILE__);
    /* Gets the current directory */
    $directory = self::getCurrentDirectory();
    /* Gets the files from the current path using 'scandir' */
    $files = self::getFiles();
    /* Is this the base directory (/)?*/
    $is_base = ($directory === '/');

    $parentDirectory = dirname($directory);
    $parentHref = $this->handlePathing($parentDirectory, true);

    if($this->pathPrepend)
    {
      $prependedCurrent = ltrim(rtrim($this->joinPaths($this->pathPrepend, $directory), '/'), '/');
      $prependedRoot = ltrim(rtrim($this->pathPrepend, '/'), '/');

      if($prependedCurrent === $prependedRoot)
      {
        $steppedPath = dirname('/' . $prependedRoot . '/');
        
        $parentHref = str_replace(
          '\\\\', '\\', $steppedPath . (substr($steppedPath, -1) === '/' ? '' : '/')
        );
      }
    }

    $op = '<tr class="parent"><td><a href="' . $parentHref . '">' . 
          '[Parent Directory]</a></td><td><span>-</span></td><td>'.
          '<span>-</span></td><td><span>-</span></td></tr>';

    $data = array(
      'files' => array(),
      'directories' => array(),
      'readme' => NULL,
      'recent' => array(
        'file' => 0,
        'directory' => 0
      ),
      'size' => array(
        'total' => 0,
        'readable' => 'N/A'
      )
    );

    /* Hide directories / files if they match the filter or if they are indexer components */
    foreach($files as $file)
    {
      if($file[0] === '.') continue;

      $path = ($this->path . '/' . $file);

      if(is_dir($path))
      {
        if($is_base && $file === 'indexer')
        {
          continue;
        } else if($this->filter['directory'] !== false && !preg_match($this->filter['directory'], $file))
        {
          continue;
        }

        array_push($data['directories'], array($path, $file)); continue;
      } else if(file_exists($path))
      {
        if($file === 'README.md')
        {
          $data['readme'] = $path;
        }
        
        if($is_base && $file === $script_name)
        {
          continue;
        } else if($this->filter['file'] !== false)
        {
          $skippable = false;

          if(is_array($this->filter['file']))
          {
            foreach($this->filter['file'] as $filter)
            {
              if(!preg_match($filter, $file))
              {
                $skippable = true; break;
              }
            }
          } else if(!$skippable) {
            $skippable = !preg_match($this->filter['file'], $file);
          }

          if($skippable)
          {
            continue;
          }
        }

        array_push($data['files'], array($path, $file)); continue;
      }
    }

    if($use_mb === true && !function_exists('mb_strtolower'))
    {
      http_response_code(500);

      die(
        'Error (mb_strtolower is not defined): In order to use mbstring, you\'ll need to ' .
        '<a href="https://www.php.net/manual/en/mbstring.installation.php">install</a> ' .
        'it first.'
      );
    }

    foreach($data['directories'] as $index => $dir)
    {
      $item = &$data['directories'][$index];

      /* We only need to set 'name' key if we're sorting by name */
      if($sort_type === 'name')
      {
        $item['name'] = $use_mb === true ? mb_strtolower($dir[1], 'UTF-8') : strtolower($dir[1]);
      }

      /* Set directory data values */
      $item['modified'] = self::getModified($dir[0], $timezone['offset']);
      $item['type'] = 'directory';
      $item['size'] = $this->directory_sizes['enabled'] ? ($this->directory_sizes['recursive'] ? self::getDirectorySizeRecursively($dir[0]) : self::getDirectorySize($dir[0])) : 0;
      $item['url'] = rtrim($this->joinPaths($this->requested, $dir[1]), '/');
    }

    foreach($data['files'] as $index => $file)
    {
      $item = &$data['files'][$index];

      /* We only need to set 'name' key if we're sorting by name */
      if($sort_type === 'name')
      {
        $item['name'] = $use_mb === true ? mb_strtolower($file[1], 'UTF-8') : strtolower($file[1]);
      }

      /* Set file data values */
      $item['type'] = self::getFileType($file[1]);
      $item['size'] = self::getSize($file[0]);
      $item['modified'] = self::getModified($file[0], $timezone['offset']);
      $item['url'] = rtrim($this->joinPaths($this->requested, $file[1]), '/');

      if($this->encode_all)
      {
        $item['url'] = str_replace('?', '%3F', str_replace('#', '%23', $item['url']));
      }
    }

    /* Pass data to processor if it is set */
    if($this->processor['item'])
    {
      $data = $this->processor['item']($data, $this);
    }

    /* Sort items server-side */
    if($sorting)
    {
      if($sort_items === 0 || $sort_items === 1)
      {
        array_multisort(
          array_column($data['files'], $sort_type),
          $sorting,
          $data['files']
        );
      }

      if($sort_items === 0 || $sort_items === 2)
      {
        array_multisort(
          array_column($data['directories'], $sort_type),
          $sorting,
          $data['directories']
        );
      }
    }

    /* Iterate over the directories, get and store data */
    foreach($data['directories'] as $dir)
    {
      if($this->directory_sizes['enabled'])
      {
        $data['size']['total'] = ($data['size']['total'] + $dir['size']);
      }

      $op .= sprintf(
        '<tr class="directory"><td data-raw="%s"><a href="%s">[%s]</a>' .
        '</td><td data-raw="%s"><span>%s</span></td>',
        $dir[1],
        $this->handlePathing($dir['url'], true),
        $dir[1],
        $dir['modified'][0],
        $dir['modified'][1]
      );

      if($data['recent']['directory'] === 0 || $dir['modified'][0] > $data['recent']['directory'])
      {
        $data['recent']['directory'] = $dir['modified'][0];
      }

      $op .= sprintf(
        '<td%s>%s</td>',
        $this->directory_sizes['enabled'] ? ' data-raw="' . $dir['size'] . '"' : '',
        $this->directory_sizes['enabled'] ? self::readableFilesize($dir['size']) : '-'
      );

      $op .= '<td><span>-</span></td></tr>';
    }

    /* Iterate over the files, get and store data */
    foreach($data['files'] as $file)
    {
      $data['size']['total'] = ($data['size']['total'] + $file['size'][0]);

      if($data['recent']['file'] === 0 || $file['modified'][0] > $data['recent']['file'])
      {
        $data['recent']['file'] = $file['modified'][0];
      }

      $op .= sprintf(
        '<tr class="file"><td data-raw="%s">',
        $file[1]
      );

      $op .= sprintf(
        '<a%shref="%s">%s</a></td>',
        (($file['type'][0] === 'image' || $file['type'][0] === 'video'
          ? true
          : false)
            ? ' class="preview" '
            : ' '),
        $this->handlePathing($file['url'], false),
        $file[1]
      );

      $op .= sprintf(
        '<td data-raw="%d"><span>%s</span></td>',
        $file['modified'][0], $file['modified'][1]
      );

      $op .= sprintf(
        '<td data-raw="%d">%s</td>',
        $file['size'][0] === -1 ? 0 : $file['size'][0], $file['size'][1]
      );

      $op .= sprintf(
        '<td data-raw="%s" class="download"><a href="%s" download="" filename="%s">%s</a></td></tr>',
        $file['type'][0], $file['url'], $file[1], ('<span data-view="mobile">[Save]</span><span data-view="desktop">[Download]</span>')
      );
    }

    $data['size']['readable'] = self::readableFilesize($data['size']['total']);

    $this->data = $data;

    return $op;
  }

  /* Gets the current files from set path */
  private function getFiles()
  {
    return scandir($this->path, SCANDIR_SORT_NONE);
  }

  /* Some data is stored in $this->data, this retrieves that */
  public function getLastData()
  {
    return isset($this->data) ? $this->data : false;
  }

  /* Gets the current directory */
  public function getCurrentDirectory()
  {
    $requested = trim($this->requested);

    if($requested === '/' || $requested === '\\' || empty($requested))
    {
      return '/';
    } else {
      return preg_replace('#/+#','/', $requested[strlen($requested) - 1] === '/' ? rtrim($requested, '/') . '/' : rtrim($requested, '/'));
    }
  }

  /* Identifies file type by matching it against the extension arrays */
  private function getFileType($filename)
  {
    $extension = strtolower(ltrim(pathinfo($filename, PATHINFO_EXTENSION), '.'));

    return array(isset($this->types[$extension]) ? $this->types[$extension] : 'other', $extension);
  }

  /* Converts the current path into clickable a[href] links */
  public function makePathClickable($path)
  {
	  $path = $this->handlePathing($path, true);
    $paths = explode('/', ltrim($path, '/'));
    $output = ('<a href="/">/</a>');

    foreach($paths as $i => $p)
    {
      $i++; $text = (($i !== 1 ? '/' : '') . $p);

      if($text === '/') continue;

      if($i === count($paths) - 1)
      {
        $text = rtrim($text, '/') . '/';
      }

      $anchor = implode('/', array_slice($paths, 0, $i));
      $output .= sprintf('<a href="/%s">%s</a>', $anchor, $text);
    }

    return $output;
  }

  /**
   * Formats a unix timestamp
   *
   * @param string    $format     String formatting
   * @param integer   $stamp      Timestamp
   * @param integer   $modifier   An integer that gets added to the timestamp
   * 
   * @return String
   */ 
  private function formatDate($format, $stamp, $modifier = 0)
  {
    return gmdate($format, $stamp + $modifier);
  }

  /**
   * Gets the last modified date of a file
   *
   * @param string    $path       File path
   * @param integer   $modifier   An integer that gets added to the timestamp
   * 
   * @return Array
   */ 
  private function getModified($path, $modifier = 0)
  {
    $stamp = filemtime($path);

    if(count($this->format['date']) === 2)
    {
      $formatted = "";

      for($i = 0; $i < 2; ++$i)
      {
        $format = self::formatDate(
          $this->format['date'][$i], $stamp, $modifier
        );

        $formatted .= sprintf(
          "<span data-view=\"%s\">%s</span>", $i === 0 ? 'desktop' : 'mobile', $format
        );
      }
    } else {
      $formatted = self::formatDate($this->format['date'][0], $stamp, $modifier);
    }

    return array($stamp, $formatted);
  }

  /**
   * Gets a client cookie key
   *
   * @param string    $path       File path
   * @param integer   $modifier   An integer that gets added to the timestamp
   * 
   * @return Array
   */ 
  private function getCookie($key, $default = NULL)
  {
    return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
  }

  /**
   * Gets the size of a file
   *
   * @param string   $path   File path
   * 
   * @return Array
   */ 
  private function getSize($path)
  {
    $fs = filesize($path);
    $size = ($fs < 0 ? -1 : $fs);

    return array($size, self::readableFilesize($size));
  }

  /**
   * Gets the size of a directory
   *
   * @param string   $path   File path
   * 
   * @return Integer
   */ 
  private function getDirectorySize($path)
  {
    $size = 0;

    try
    {
      foreach(scandir($path, SCANDIR_SORT_NONE) as $file)
      {
        if($file[0] === '.')
        {
          continue;
        } else {
          $filesize = filesize($this->joinPaths($path, $file));

          if($filesize && $filesize > 0)
          {
            $size += $filesize;
          }
        }
      }
    } catch (Exception $e)
    {
      $size += 0;
    }

    return $size;
  }

  /**
   * Gets the full size of a director using recursive scanning
   *
   * @param string   $path   File path
   * 
   * @return Integer
   */ 
  private function getDirectorySizeRecursively($path)
  {
    $size = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    try
    {
      foreach($iterator as $file)
      {
        if($file->isDir())
        {
          continue;
        } else {
          $size += filesize($file->getPathname());
        }
      }
    } catch (Exception $e)
    {
      $size += 0;
    }

    return $size;
  }

  /**
   * Converts bytes to a readable file size
   *
   * @param integer   $bytes      File size in bytes
   * @param integer   $decimals   # of decimals in the readable output
   * 
   * @return String
   */ 
  private function readableFilesize($bytes, $decimals = 1)
  {
    if($bytes === 0)
    {
      return '0' . $this->format['sizes'][0];
    }

    $base = log($bytes, 1024);
    $floored = floor($base);
    $value = pow(1024, $base - $floored);

    if($value >= 100)
    {
      $decimals = 0;
    }

    return round($value, $decimals) . $this->format['sizes'][$floored];
  }
}

/* Is cookie set? */
$client = isset($_COOKIE['IVFi']) ? $_COOKIE['IVFi'] : NULL;

/* If client cookie is set, parse it */
if($client)
{
  $client = json_decode($client, true);
}

/* Validate that the cookie is a valid array */
$validate = is_array($client);

$cookies = array(
  'readme' => array(
    'toggled' => isset($client['readme']['toggled']) ? $client['readme']['toggled'] : true
  ),
  'sorting' => array(
    'row' => $validate
      ? (isset($client['sort']['row']) ? $client['sort']['row'] : NULL)
      : NULL,
    'ascending' => $validate
      ? (isset($client['sort']['ascending']) ? $client['sort']['ascending'] : NULL)
      : NULL
  )
);

/* Override the config value if the cookie value is set */
if($validate && isset($client['style']['compact']) && $client['style']['compact'])
{
  $config['style']['compact'] = $client['style']['compact'];
}

/* Set sorting settings */
$sorting = array(
  'enabled' => $config['sorting']['enabled'],
  'order' => $config['sorting']['order'],
  'types' => $config['sorting']['types'],
  'sort_by' => strtolower($config['sorting']['sort_by'])
);

if($cookies['sorting']['row'] !== NULL)
{
  switch(intval($cookies['sorting']['row']))
  {
    case 0: $sorting['sort_by'] = 'name'; break;
    case 1: $sorting['sort_by'] = 'modified'; break;
    case 2: $sorting['sort_by'] = 'size'; break;
    case 3: $sorting['sort_by'] = 'type'; break;
  }
}

if($cookies['sorting']['ascending'] !== NULL)
{
  $sorting['order'] = (boolval($cookies['sorting']['ascending']) === true ? SORT_ASC : SORT_DESC);
}

if($cookies['sorting']['ascending'] !== NULL || $cookies['sorting']['row'] !== NULL)
{
  $sorting['enabled'] = true;
}

/* Get `INDEXER_PREPEND_PATH` if set */
if(isset($_SERVER['INDEXER_PREPEND_PATH']))
{
  $prependPath = $_SERVER['INDEXER_PREPEND_PATH'];
} else if(isset($_SERVER['HTTP_X_INDEXER_PREPEND_PATH']))
{
  $prependPath = $_SERVER['HTTP_X_INDEXER_PREPEND_PATH'];
} else {
  $prependPath = '';
}

try
{
  /* Call class with options set */
  $indexer = new Indexer(
      CURRENT_URI,
      array(
          'path' => array(
            'relative' => BASE_PATH,
            'prepend' => $prependPath
          ),
          'format' => array(
            'date' => isset($config['format']['date']) ? $config['format']['date'] : NULL,
            'sizes' => isset($config['format']['sizes']) ? $config['format']['sizes'] : NULL
          ),
          'directory_sizes' => $config['directory_sizes'],
          'client' => $client,
          'filter' => $config['filter'],
          'extensions' => $config['extensions'],
          'path_checking' => strtolower($config['path_checking']),
          'processor' => $config['processor'],
          'encode_all' => $config['encode_all'],
          'allow_direct_access' => $config['allow_direct_access']
      )
  );
} catch (Exception $e) {
  http_response_code(500);

  echo "<h3>Error:</h3><p>{$e} ({$e->getCode()})</p>";

  if($e->getCode() === 1 || $e->getCode() === 2)
  {
    echo '<p>This error occurs when the requested directory is below the directory of the PHP file.'.
    ($e->getCode() === 1 ? '<br/>You can try setting <b>path_checking</b> to <b>weak</b> if you are working with symbolic links etc.' : '') . '</p>';
  }

  exit('<p>Fatal error - Exiting.</p>');
}

/* Call 'buildTable', get content */
$contents = $indexer->buildTable(
  $sorting['enabled'] ? $sorting['order'] : false,
  $sorting['enabled'] ? $sorting['types'] : 0,
  $sorting['enabled'] ? strtolower($sorting['sort_by']) : 'modified',
  $sorting['enabled'] ? $config['sorting']['use_mbstring'] : false
);

$data = $indexer->getLastData();

$itemsTotal = (count($data['files']) + count($data['directories']));

/* Check if performance mode depends on item count */
if(is_int($config['performance']))
{
  $itemsTotal = (count($data['files']) + count($data['directories']));

  if($itemsTotal >= $config['performance'])
  {
    $config['performance'] = true;
  } else {
    $config['performance'] = false;
  }
}

/* Set some data like file count etc */
$counts = array(
    'files' => count($data['files']),
    'directories' => count($data['directories'])
);

$themes = array(
  'default' => array(
    'path' => NULL
  )
);

if($config['style']['themes']['path'])
{
  $themesPool = getThemes(BASE_PATH, $config['style']['themes']['path']);

  if($themesPool
    && is_array($themesPool)
    && count($themesPool) > 0)
  {
    $themes = array_merge($themes, $themesPool);
  }
}

/**
 * Set current theme if available
 */
$currentTheme = NULL;

if(count($themes) > 0)
{
  /* Check if client has a custom theme already set */
  if(is_array($client)
    && isset($client['style']['theme']))
  {
    $currentTheme = $client['style']['theme'] ? $client['style']['theme'] : NULL;
  /* Check for a default theme */
  } else if(isset($config['style']['themes']['default']))
  {
    $defaultTheme = strtolower($config['style']['themes']['default']);

    if($defaultTheme && isset($themes[$defaultTheme]))
    {
      $currentTheme = $defaultTheme;
    }
  }
}

$compact = NULL;

/* Apply compact mode if that is set */
if(is_array($client) && isset($client['style']['compact']))
{
  $compact = $client['style']['compact'];
} else {
  $compact = $config['style']['compact'];
}

/* Used to bust the cache (query-strings for js and css files) */
$bust = md5($config['debug'] ? time() : $version);

/* Set any additional CSS */
$additionalCss = "";

if(is_array($config['style']['css']['additional']))
{
  foreach($config['style']['css']['additional'] as $key => $value)
  {
    $selector = $key;
    $values = (string) NULL;

    foreach($value as $key => $value)
    {
      $values .= sprintf('%s:%s;', $key, rtrim($value, ';'));
    }

    $additionalCss .= sprintf('%s{%s}', $selector, $values);
  }
} else if(is_string($config['style']['css']['additional']))
{
  $additionalCss .= str_replace('"', '\"', $config['style']['css']['additional']);
}

/* Default stylesheet output */
$baseStylesheet = '<style type="text/css">html[gallery-is-visible]>div:not(.rootGallery,script,noscript,style){pointer-events:none;-webkit-user-select:none;-moz-user-select:none;user-select:none}html>body>.rootGallery{background-color:rgba(4,4,4,.8);height:100vh;left:0;max-height:100vh;max-width:100vw;position:fixed;top:0;width:100%;z-index:1000}html>body>.rootGallery>div.galleryBar{-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);background-color:rgba(25,25,32,.7);border-bottom:2px solid hsla(0,0%,9%,.871);color:#fff;display:table;height:29px;max-height:29px;min-height:29px;width:100%}html>body>.rootGallery>div.galleryBar>div{display:table-cell;vertical-align:middle}html>body>.rootGallery>div.galleryBar>.galleryBarRight{padding-right:10px;text-align:right;white-space:nowrap;width:1%}html>body>.rootGallery>div.galleryBar>.galleryBarRight>a,html>body>.rootGallery>div.galleryBar>.galleryBarRight>span[data-action]{-webkit-user-select:none;-moz-user-select:none;user-select:none}html>body>.rootGallery>div.galleryBar>.galleryBarRight>span[data-action]{margin:0 2px}html>body>.rootGallery>div.galleryBar>.galleryBarRight>span[data-action=toggle]>span{display:inline-block;text-align:center;width:13px}html>body>.rootGallery>div.galleryBar>.galleryBarRight>a.download{color:#6173c5;text-decoration:none}html>body>.rootGallery>div.galleryBar>.galleryBarRight>a.download:hover{color:#6a83f5;text-decoration:underline}html>body>.rootGallery>div.galleryBar>.galleryBarRight>:not(:last-child){margin-right:3px}html>body>.rootGallery>div.galleryBar>.galleryBarRight>a.download,html>body>.rootGallery>div.galleryBar>.galleryBarRight>span[data-action]{cursor:pointer}html>body>.rootGallery>div.galleryBar>.galleryBarRight>a.download:before,html>body>.rootGallery>div.galleryBar>.galleryBarRight>span[data-action]:before{content:"["}html>body>.rootGallery>div.galleryBar>.galleryBarRight>a.download:after,html>body>.rootGallery>div.galleryBar>.galleryBarRight>span[data-action]:after{content:"]"}html>body>.rootGallery>div.galleryBar>div.galleryBarLeft{font-size:13px;letter-spacing:1px;max-width:0;overflow:hidden;padding:0 10px;text-overflow:ellipsis;white-space:nowrap}@media only screen and (max-width:768px){html>body>.rootGallery>div.galleryBar>div.galleryBarLeft{font-size:11px}}html>body>.rootGallery>div.galleryBar>div.galleryBarLeft>span:first-child{font-feature-settings:"tnum"}html>body>.rootGallery>div.galleryBar>div.galleryBarLeft>span:first-child:after,html>body>.rootGallery>div.galleryBar>div.galleryBarLeft>span:last-child:before{color:#1c1c1c;content:"|";margin:0 7px}html>body>.rootGallery>div.galleryBar>div.galleryBarLeft>a{color:#fff;text-decoration:none}html>body>.rootGallery>div.galleryBar>div.galleryBarLeft>a:hover{text-decoration:underline}html>body>.rootGallery>div.galleryContent{display:table;height:calc(100vh - 31px);max-height:calc(100vh - 33px);width:100vw}html>body>.rootGallery>div.galleryContent>div.list>div.drag{border-left:2px solid rgba(28,28,28,.87);bottom:0;content:" ";cursor:w-resize;min-height:100%;position:-webkit-sticky;position:sticky;top:0;width:5px;z-index:1}html>body>.rootGallery>div.galleryContent.reversed>div.list>div.drag{border-left:none;border-right:2px solid hsla(0,0%,9%,.871);bottom:0;float:right;left:unset;right:0;top:0}html>body>.rootGallery>div.galleryContent.reversed>div.media>div.spinner{top:10px}html>body>.rootGallery>div.galleryContent:not(.reversed)>div.media>div.spinner{top:35px}html>body>.rootGallery>div.galleryContent>div.list>table{bottom:0;max-width:100%;position:absolute;top:0;width:100%}html>body>.rootGallery>div.galleryContent.reversed>div.list>table>tbody>tr.selected>td{background-color:#1c2333;border-left:none;border-right:5px solid #fbfcff}html>body>.rootGallery>div.galleryContent>div.list>table>tbody>tr:not(.selected)>td:hover{background-color:rgba(44,54,81,.2);color:auto}html>body>.rootGallery>div.galleryContent>div.screenNavigate{display:none}html>body>.rootGallery>div.galleryContent>div.screenNavigate>span{pointer-events:none}html>body>.rootGallery>div.galleryContent>div.media{-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);display:table-cell;position:relative;text-align:center;-webkit-user-select:none;-moz-user-select:none;user-select:none;vertical-align:middle;width:100%}html>body>.rootGallery>div.galleryContent>div.media>div.item-info-static{font-feature-settings:"tnum";background-color:rgba(0,0,0,.5);border-radius:2px;opacity:1;padding:1px 5px 2px;pointer-events:none;position:absolute;right:5px;top:5px;transition:opacity .5s;transition:opacity .5s ease-in-out}html>body>.rootGallery>div.galleryContent>div.media>div.wrapper>div.cover>div.reverse{border-radius:2px;font-size:11px;left:4px;opacity:.5;overflow:hidden;pointer-events:none;position:absolute;top:4px;transition:opacity .1s;transition:opacity .1s ease-in-out;visibility:hidden}html>body>.rootGallery>div.galleryContent>div.media>div.wrapper>div.cover>div.reverse:hover{opacity:.85}html>body>.rootGallery>div.galleryContent>div.media>div.wrapper>div.cover:hover>div.reverse{visibility:visible}html>body>.rootGallery>div.galleryContent>div.media>div.wrapper>div.cover>.reverse>a{background-color:#151515;color:#e5e5e5;display:inline-block;padding:3px 6px 4px;pointer-events:auto;text-decoration:none;transition:background-color .2s;transition:background-color .2s ease-in-out}html>body>.rootGallery>div.galleryContent>div.media>div.wrapper>div.cover>.reverse>a:not(:last-child){border-right:1px solid #1a1a1a}html>body>.rootGallery>div.galleryContent>div.media>div.wrapper>div.cover>.reverse>a:hover{background-color:rgba(0,0,0,.975);color:#fff}html>body>.rootGallery>div.galleryContent>div.media>div.spinner{background:url(\'data:image/svg+xml;utf8,<svg width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a"><stop stop-color="%23fff" stop-opacity="0" offset="0%"/><stop stop-color="%23fff" stop-opacity=".631" offset="63.146%"/><stop stop-color="%23fff" offset="100%"/></linearGradient></defs><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)"><path d="M36 18c0-9.94-8.06-18-18-18" id="Oval-2" stroke="url(%23a)" stroke-width="2"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite" /></path><circle fill="%23fff" cx="36" cy="18" r="1"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite" /></circle></g></g></svg>\');background-size:26px 26px;height:26px;opacity:0;position:absolute;right:10px;transition:opacity .25s;transition:opacity .25s ease-in-out;width:26px;z-index:1}html>body>.rootGallery>div.galleryContent>div.media>div.loader{background-color:rgba(0,0,0,.631);border-radius:2px;padding:7px 9px;position:absolute;right:10px;top:10px}html>body>.rootGallery>div.galleryContent.reversed>.media>.spinner{left:10px;right:unset}html>body>.rootGallery>div.galleryContent>.media>.wrapper{align-items:center;display:flex;flex-flow:column wrap;justify-content:center;max-height:calc(100vh - 33px)}html>body>.rootGallery>div.galleryContent>.media>.wrapper div.error{color:#d83232;display:block;width:100%}html>body>.rootGallery>div.galleryContent>.media>.wrapper>.cover,html>body>.rootGallery>div.galleryContent>.media>.wrapper>.cover>img,html>body>.rootGallery>div.galleryContent>.media>.wrapper>.cover>video{max-height:calc(100vh - 33px)}html>body>.rootGallery>div.galleryContent>.media>.wrapper>.cover{position:relative}html>body>.rootGallery>div.galleryContent>.media>.wrapper.fill{height:calc(100vh - 33px)}html>body>.rootGallery>div.galleryContent>.media>.wrapper.fill>.cover{height:100%}html>body>.rootGallery>div.galleryContent>.media>.wrapper.fill>img,html>body>.rootGallery>div.galleryContent>.media>.wrapper.fill>video{height:100%;-o-object-fit:contain;object-fit:contain;overflow:hidden}html>body>.rootGallery>div.galleryContent>.media>.wrapper.fill>img{width:100%}html>body>.rootGallery>div.galleryContent>.media>.wrapper.fill>video{width:auto}html>body>.rootGallery>div.galleryContent>.media>.wrapper>.cover>img{display:none;max-width:100%;-o-object-fit:contain;object-fit:contain}html>body>.rootGallery>div.galleryContent>.media>.wrapper>.cover>img[sharpened]{image-rendering:optimizequality;transform:translateZ(0)}html>body>.rootGallery>div.galleryContent>.media>.wrapper>video{border:none;display:none;max-width:100%;-o-object-fit:contain;object-fit:contain;outline:none}html>body>.rootGallery>div.galleryContent>div.list{background-color:#101013;border-top:1px solid hsla(0,0%,6%,.722);display:table-cell;float:right;height:calc(100vh - 33px);max-height:calc(100vh - 33px);max-width:50vw;min-width:275px;overflow-x:hidden;overflow-y:scroll;position:relative;scrollbar-color:#222 #131315;width:25vw}html>body>.rootGallery>div.galleryContent>div.list>table>tbody>tr{color:#d4d4d4;font-size:13px;letter-spacing:1px}html>body>.rootGallery>div.galleryContent>div.list>table>tbody>tr>td{border-left:3px solid transparent;cursor:pointer;max-width:0;overflow:hidden;padding:6px 4px 6px 6px;text-overflow:ellipsis;-webkit-user-select:none;-moz-user-select:none;user-select:none;white-space:nowrap}html>body>.rootGallery>div.galleryContent>div.list>table>tbody>tr.selected>td{background-color:#1c2333;border-left:5px solid #fbfcff;color:#fff}html>body>.rootGallery>div.galleryContent>div.list>table>tbody>tr.selected>td:hover{background-color:#1c2333}@media only screen and (max-width:768px){html>body>.rootGallery>div.galleryContent>div.list>table>tbody>tr{font-size:11px}}html>body>.rootGallery>div.galleryBar>div.galleryBarRight>a.download,html>body>.rootGallery>div.galleryBar>div.galleryBarRight>span[data-action]{font-size:13px;letter-spacing:1px}@media only screen and (max-width:768px){html>body>.rootGallery>div.galleryBar>div.galleryBarRight>a.download,html>body>.rootGallery>div.galleryBar>div.galleryBarRight>span[data-action]{font-size:11px}}*,:after,:before{box-sizing:inherit}::-webkit-scrollbar{background-color:#131315;width:10px}::-webkit-scrollbar-thumb{background-color:#424242}::-webkit-scrollbar-thumb:hover{background-color:#383838}select:not(.default)::-ms-expand{display:none}select:not(.default):focus,select:not(.default):hover{background-color:#262835;border:1px solid #393c4a}select:not(.default):focus{outline:none}@font-face{font-display:swap;font-family:Inter;font-style:normal;font-weight:400;src: url(data:application/font-woff2;charset=utf-8;base64,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);unicode-range:u+0460-052f,u+1c80-1c88,u+20b4,u+2de0-2dff,u+a640-a69f,u+fe2e-fe2f}@font-face{font-display:swap;font-family:Inter;font-style:normal;font-weight:400;src: url(data:application/font-woff2;charset=utf-8;base64,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);unicode-range:u+0400-045f,u+0490-0491,u+04b0-04b1,u+2116}@font-face{font-display:swap;font-family:Inter;font-style:normal;font-weight:400;src: url(data:application/font-woff2;charset=utf-8;base64,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);unicode-range:u+1f??}@font-face{font-display:swap;font-family:Inter;font-style:normal;font-weight:400;src: url(data:application/font-woff2;charset=utf-8;base64,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);unicode-range:u+0370-03ff}@font-face{font-display:swap;font-family:Inter;font-style:normal;font-weight:400;src: url(data:application/font-woff2;charset=utf-8;base64,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);unicode-range:u+0102-0103,u+0110-0111,u+0128-0129,u+0168-0169,u+01a0-01a1,u+01af-01b0,u+1ea0-1ef9,u+20ab}@font-face{font-display:swap;font-family:Inter;font-style:normal;font-weight:400;src: url(data:application/font-woff2;charset=utf-8;base64,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);unicode-range:u+0100-024f,u+0259,u+1e??,u+2020,u+20a0-20ab,u+20ad-20cf,u+2113,u+2c60-2c7f,u+a720-a7ff}@font-face{font-display:swap;font-family:Inter;font-style:normal;font-weight:400;src: url(data:application/font-woff2;charset=utf-8;base64,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);unicode-range:u+00??,u+0131,u+0152-0153,u+02bb-02bc,u+02c6,u+02da,u+02dc,u+2000-206f,u+2074,u+20ac,u+2122,u+2191,u+2193,u+2212,u+2215,u+feff,u+fffd}html>body>.settingsContainer>.wrapper>div.section .option>div.option-text,html>body>div.tableContainer>table [sortable]{user-select:none}body,input,select,select>option{font-family:Inter,sans-serif;font-weight:400}html,html>body{scrollbar-color:#222 #131315}html>body{background-color:#0f0f13;color:#d6d3ce;cursor:auto;font-size:13px;margin:0;padding-top:29px;position:relative}html>body .readmeContainer{background-color:#121318;border-bottom:1px solid #19191d;border-top:1px solid #19191d;margin-bottom:18px;text-align:center}html>body .readmeContainer>.readmeContents{padding:5px 8px;position:relative}html>body .readmeContainer a{color:#d6d3ce;font-weight:700;text-decoration:none}html>body .readmeContainer a:hover{color:#5e74ea;text-decoration:underline}html>body .readmeContainer:not([open]) summary:before{transform:rotate(180deg)}html>body .readmeContainer summary{background-color:#15161e;border-bottom:1px solid #151822;color:#939498;cursor:pointer;display:block;padding:3px 8px 5px 6px;text-align:right;-webkit-user-select:none;-moz-user-select:none;user-select:none}html>body .readmeContainer summary:before{content:"▾";display:inline-block;margin-right:5px;transform:rotate(0deg);transition:transform .25s}html>body .readmeContainer pre{line-height:145%;white-space:pre-wrap}html>body>div.navigateLoad{background:url(\'data:image/svg+xml;utf8,<svg width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a"><stop stop-color="%23fff" stop-opacity="0" offset="0%"/><stop stop-color="%23fff" stop-opacity=".631" offset="63.146%"/><stop stop-color="%23fff" offset="100%"/></linearGradient></defs><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)"><path d="M36 18c0-9.94-8.06-18-18-18" id="Oval-2" stroke="url(%23a)" stroke-width="2"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite" /></path><circle fill="%23fff" cx="36" cy="18" r="1"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite" /></circle></g></g></svg>\');background-size:26px 26px;bottom:10px;height:26px;opacity:0;position:fixed;right:10px;transition:opacity .1s;transition:opacity .1s ease-in-out;width:26px;z-index:99}html>body>div.tableContainer[is-active-filter]>table>tbody>tr.filtered{display:none}@media only screen and (max-width:768px){html>body>div.tableContainer>table>thead>tr>th:first-child{padding-left:4px!important}}html>body>div.tableContainer>table{background-color:#121318;border-spacing:0;table-layout:fixed;width:100%}html>body>div.tableContainer>table>thead>tr{background-color:#15161e;border-bottom:2px solid #151822;padding:4px 10px 2px}html>body>div.tableContainer>table>tbody>tr{padding:0 10px}html>body>div.tableContainer>table>tbody>tr,html>body>div.tableContainer>table>thead>tr{width:100%}html>body>div.tableContainer>table>tbody>tr:not(.hid-row),html>body>div.tableContainer>table>thead>tr:not(.hid-row){display:flex}html>body>div.tableContainer>table>tbody>tr.directory>td>a,html>body>div.tableContainer>table>thead>tr.directory>td>a{color:#f45656}html>body>div.tableContainer>table>tbody>tr.parent>td,html>body>div.tableContainer>table>thead>tr.parent>td{padding-top:8px}html>body>div.tableContainer>table>tbody>tr.parent>td>a,html>body>div.tableContainer>table>thead>tr.parent>td>a{color:#ffc752}html>body>div.tableContainer>table>tbody>tr.directory>td:first-child,html>body>div.tableContainer>table>tbody>tr.file>td:first-child,html>body>div.tableContainer>table>tbody>tr.parent>td:first-child,html>body>div.tableContainer>table>thead>tr.directory>td:first-child,html>body>div.tableContainer>table>thead>tr.file>td:first-child,html>body>div.tableContainer>table>thead>tr.parent>td:first-child{padding-right:5px}html>body>div.tableContainer>table>tbody>tr.file>td>a:not(.preview),html>body>div.tableContainer>table>thead>tr.file>td>a:not(.preview){color:#9b9b9b}html>body>div.tableContainer>table>tbody>tr.file>td>a:not(.preview):visited,html>body>div.tableContainer>table>thead>tr.file>td>a:not(.preview):visited{color:#757575}html>body>div.tableContainer>table>tbody>tr.file>td>a.preview,html>body>div.tableContainer>table>tbody>tr.file>td>a.preview:hover,html>body>div.tableContainer>table>thead>tr.file>td>a.preview,html>body>div.tableContainer>table>thead>tr.file>td>a.preview:hover{color:#627be3}html>body>div.tableContainer>table>tbody>tr.file>td>a.preview:visited,html>body>div.tableContainer>table>thead>tr.file>td>a.preview:visited{color:#475282}html>body>div.tableContainer>table>tbody>tr.file>td.download>a,html>body>div.tableContainer>table>thead>tr.file>td.download>a{color:#6270ae}html>body>div.tableContainer>table>tbody>tr.file>td.download>a:visited,html>body>div.tableContainer>table>thead>tr.file>td.download>a:visited{color:#515978}html>body>div.tableContainer>table>tbody>tr>td,html>body>div.tableContainer>table>thead>tr>td{padding:7px 0}html>body>div.tableContainer>table>tbody>tr>td,html>body>div.tableContainer>table>tbody>tr>th,html>body>div.tableContainer>table>thead>tr>td,html>body>div.tableContainer>table>thead>tr>th{overflow:hidden;padding:5px 0;text-overflow:ellipsis;white-space:nowrap}html>body>div.tableContainer>table>tbody>tr>td:last-child,html>body>div.tableContainer>table>tbody>tr>th:last-child,html>body>div.tableContainer>table>thead>tr>td:last-child,html>body>div.tableContainer>table>thead>tr>th:last-child{text-transform:capitalize}@media only screen and (max-width:768px){html>body>div.tableContainer>table>tbody>tr,html>body>div.tableContainer>table>thead>tr{padding:0 4px}html>body>div.tableContainer>table>tbody>tr>td:first-child,html>body>div.tableContainer>table>thead>tr>th:first-child{max-width:40.5%;min-width:40.5%;padding-left:4px!important;width:40.5%}}@media only screen and (min-width:768px){html>body>div.tableContainer>table>tbody>tr>td:first-child,html>body>div.tableContainer>table>thead>tr>th:first-child{max-width:50%;min-width:50%;width:50%}}@media only screen and (max-width:768px){html>body>div.tableContainer>table>tbody>tr>td:nth-child(2),html>body>div.tableContainer>table>thead>tr>th:nth-child(2){max-width:21%;min-width:21%;padding-left:4px!important;width:21%}}@media only screen and (min-width:768px){html>body>div.tableContainer>table>tbody>tr>td:nth-child(2),html>body>div.tableContainer>table>thead>tr>th:nth-child(2){max-width:20%;min-width:20%;width:20%}}@media only screen and (max-width:768px){html>body>div.tableContainer>table>tbody>tr>td:nth-child(3),html>body>div.tableContainer>table>thead>tr>th:nth-child(3){max-width:19%;min-width:19%;padding-left:4px!important;width:19%}}@media only screen and (min-width:768px){html>body>div.tableContainer>table>tbody>tr>td:nth-child(3),html>body>div.tableContainer>table>thead>tr>th:nth-child(3){max-width:15%;min-width:15%;width:15%}}@media only screen and (max-width:768px){html>body>div.tableContainer>table>tbody>tr>td:nth-child(4),html>body>div.tableContainer>table>thead>tr>th:nth-child(4){max-width:19.5;min-width:19.5;padding-left:4px!important;width:19.5}}@media only screen and (min-width:768px){html>body>div.tableContainer>table>tbody>tr>td:nth-child(4),html>body>div.tableContainer>table>thead>tr>th:nth-child(4){max-width:11.5%;min-width:11.5%;width:11.5%}}html>body>div.tableContainer>table>thead>tr>th{color:#fff;font-size:13px;letter-spacing:1px;padding:4px 0 6px 1px;text-align:left}@media only screen and (max-width:768px){html>body>div.tableContainer>table>thead>tr>th{font-size:10px}}html>body>div.tableContainer>table>thead>tr>th>span[sortable]{cursor:pointer;text-decoration:none}html>body>div.tableContainer>table>thead>tr>th>span[sortable]:hover{text-decoration:underline}html>body>div.tableContainer>table>thead>tr>th>span.sortingIndicator{cursor:default;margin-left:6px;opacity:0;text-decoration:none;transition:opacity .5s}html>body>div.tableContainer>table>thead>tr>th>span.sortingIndicator.visible{opacity:1}html>body>div.tableContainer>table>thead>tr>th>span.sortingIndicator.up:after{content:"↑"}html>body>div.tableContainer>table>thead>tr>th>span.sortingIndicator.down:after{content:"↓"}html>body>div.tableContainer>table>tbody>tr>td{font-size:13px;font-weight:500;letter-spacing:1px;padding:5px 0;text-decoration:none}@media only screen and (max-width:768px){html>body>div.tableContainer>table>tbody>tr>td{font-size:11px}}html>body>div.tableContainer>table>tbody>tr>td:nth-child(2),html>body>div.tableContainer>table>tbody>tr>td:nth-child(3){font-feature-settings:"tnum";font-size:13px}@media only screen and (max-width:768px){html>body>div.tableContainer>table>tbody>tr>td:nth-child(2),html>body>div.tableContainer>table>tbody>tr>td:nth-child(3),html>body>div.tableContainer>table>tbody>tr>td:nth-child(4){font-size:11px}}html>body>div.tableContainer>table>tbody>tr>td>a{text-decoration:none}html>body>div.tableContainer>table>tbody>tr>td>a:hover{text-decoration:underline}html>body>div.tableContainer>table>tbody{padding-top:1px}html>body[optimize]>div.rootGallery>div.galleryContent>div.list>table>tbody>tr.hid-row{display:none}html>body[optimize]>div.rootGallery>div.galleryContent>div.list>table>tbody>tr>td{max-width:inherit!important}html>body[optimize]>div.tableContainer>table>tbody>tr.rel-row{position:absolute}html>body[optimize]>div.rootGallery>div.galleryContent>div.list>table>tbody>tr.rel-row,html>body[optimize]>div.rootGallery>div.galleryContent>div.list>table>tbody>tr.rel-row>td{left:0;position:absolute;right:0}html>body[optimize]>div.tableContainer>table>tbody>tr.rel-row>td{overflow:hidden}html>body[optimize]>div.tableContainer>table>tbody>tr.hid-row{display:none}html>body>div.preview-container video{visibility:hidden}html>body>div#indicatorPreviewVolume{font-feature-settings:"tnum";background-color:rgba(0,0,0,.35);border-radius:4px;bottom:10px;letter-spacing:1px;opacity:0;padding:5px 10px;pointer-events:none;position:fixed;right:10px;transition:opacity .33s;z-index:10000}html>body>div.bottom{border-top:1px solid #19191d;color:#3d414a;font-size:13px;letter-spacing:1px;padding:8px 0 10px 10px;position:relative}html>body>div.bottom>div{display:inline-block}html>body>div.bottom a{color:#3d414a;text-decoration:none}html>body>div.bottom a:hover{color:#3d414a;text-decoration:underline}html>body>div.bottom>div.currentPageInfo>span.generationTime{font-feature-settings:"tnum"}html>body>div.bottom div:first-child:not(.referenceGit):after{content:"|";margin:0 10px}html>body>div.bottom div:nth-child(2){text-overflow:ellipsis}html>body>div.bottom div:nth-child(2)>span{display:inline-block;overflow-x:hidden;text-overflow:ellipsis;vertical-align:top;white-space:nowrap}html>body>div.path{font-size:19px;font-style:normal;letter-spacing:1px;margin:20px 0 16px 8px;overflow:hidden;padding-right:10px;text-overflow:ellipsis;white-space:nowrap}html>body>div.path>a{color:#fff;font-style:normal;font-weight:700;text-decoration:none}html>body>div.path>a:hover{text-decoration:underline}html>body>div.filterContainer{border:none;bottom:0;box-shadow:0 0 6px #10151f;display:none;left:0;padding:0;position:fixed;right:0;width:100%}html>body>div.filterContainer>input[type=text]{background-color:rgba(18,18,22,.941);border:none;border-radius:1px;border-top:2px solid #1d1f27;color:#fff;font-size:14px;height:24px;letter-spacing:1px;padding:5px 7px 5px 9px;width:100%}@media only screen and (max-width:768px){html>body>div.filterContainer>input[type=text]{font-size:12px}}html>body>div.filterContainer>input[type=text]:focus{outline:none}html>body>div.settingsContainer{background-color:rgba(18,18,22,.941);border:1px solid #19191d;border-radius:3px;box-shadow:0 0 10px #0d0d0d;font-size:13px;letter-spacing:1px;min-width:300px;padding-top:10px;position:fixed;right:20px;top:20px;z-index:4}html>body>div.settingsContainer>.wrapper{max-height:75vh;overflow-y:auto}html>body>div.settingsContainer>.wrapper>div.section{padding:10px 0}html>body>div.settingsContainer>.wrapper>div.section>div.header{background-color:#17171d;border-bottom:1px solid #1d1d25;border-top:1px solid #1d1d25;box-shadow:0 0 1px #0d0d0d;color:#797979;font-size:13px;margin-bottom:14px;padding:4px 17px}html>body>div.settingsContainer>.wrapper>div.section:first-child{padding-top:0}html>body>div.settingsContainer>.wrapper>div.section .option{display:table;height:25px;padding:0 10px;width:calc(100% - 20px)}html>body>div.settingsContainer>.wrapper>div.section .option.interactable,html>body>div.settingsContainer>.wrapper>div.section .option.interactable input{cursor:pointer}html>body>div.settingsContainer>.wrapper>div.section .option:not(:first-child){margin-top:10px}html>body>div.settingsContainer>.wrapper>div.section .option>div{display:table-cell;width:50%}html>body>div.settingsContainer>.wrapper>div.section .option>div:last-child{text-align:right;width:auto}html>body>div.settingsContainer>div.bottom{background-color:#17171d;border-top:1px solid #1d1d25;display:table;margin-top:4px;width:100%}html>body>div.settingsContainer>div.bottom>div{display:table-cell;font-size:12px;padding:6px 7px 8px;text-align:center}html>body>div.settingsContainer>div.bottom>div:first-child{border-right:1px solid #1c1c1d}html>body>div.settingsContainer>div.bottom>div:not(:last-child){border-right:1px solid #292929;width:50%}html>body>div.settingsContainer>div.bottom>div:hover{background-color:#1c1c25;cursor:pointer}html>body>div.topBar>div.directoryInfo>div.quickPath{max-width:50vw;opacity:0;overflow-x:hidden;position:absolute;text-overflow:ellipsis;transition:opacity .175s;vertical-align:middle;white-space:nowrap}html>body>div.topBar>div.directoryInfo>div.quickPath.visible{opacity:1}html>body>div.topBar>div.directoryInfo>div.quickPath:not(.visible){pointer-events:none}html>body>div.topBar>div.directoryInfo>div.quickPath>a{color:#fff;text-decoration:none}html>body>div.topBar>div.directoryInfo>div.quickPath>a:hover{text-decoration:underline}html>body>div.tableContainer{background-color:#121318;border-top:1px solid #19191d;overflow:hidden;padding-bottom:10px;position:relative}html>body>div.bottom>div.referenceGit{font-style:italic;position:absolute;right:10px}html>body>div.bottom>div.referenceGit>span{margin-left:6px}html>body:not(.compact)>div.bottom>div.referenceGit.single{position:relative;text-align:right;width:100%}html>body.compact>div.tableContainer{width:100%}html>body.compact>div.path{font-size:19px;font-style:normal;letter-spacing:1px;margin:24px 0 22px 8px;text-align:center;-webkit-user-select:none;-moz-user-select:none;user-select:none}html>body.compact>div.bottom>div.referenceGit.single{position:relative;text-align:right;width:100%}html>body>div.topBar{-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);background-color:rgba(18,18,22,.65);border-bottom:1px solid hsla(0,0%,6%,.522);box-shadow:0 0 3px #0d0d0d;color:#939498;display:table;font-size:12px;left:0;max-width:100%;padding:2px 0;position:fixed;right:0;top:0;width:100%;z-index:2}html>body>div.topBar>div.directoryInfo{display:inline-table;font-size:12px;letter-spacing:1px}html>body>div.topBar>div.directoryInfo>div{display:table-cell;padding:6px 8px;white-space:nowrap}html>body>div.topBar>div.directoryInfo>div:not(.quickPath){border-right:2px solid rgba(255,255,255,.01)}html>body>div.topBar>div.extend{align-items:center;border-left:2px solid hsla(0,0%,6%,.522);bottom:0;display:flex;font-size:1.5em;font-weight:700;justify-content:center;padding:0 9px;position:absolute;right:0;top:0;transition:color .2s;user-select:none;vertical-align:middle;white-space:nowrap}html>body>div.topBar>div.extend:hover{background-color:hsla(0,0%,6%,.522);color:#fff;cursor:pointer}html>body>div.topBar>div.extend[extended]{color:#d6d3ce}html>body>div.menu{-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);background-color:rgba(18,18,22,.65);border-bottom:1px solid #17171b;border-left:1px solid #17171b;box-shadow:0 0 2px #0d0d0d;color:#939498;font-size:12px;position:fixed;right:0;text-align:left;top:29px;user-select:none;visibility:hidden}html>body>div.menu>div{border-left:3px solid transparent;cursor:pointer;font-size:13px;letter-spacing:1px;padding:8px 18px 8px 7px}html>body>div.menu>div:hover{background-color:#17171b;border-left:3px solid #939393;color:#bfbfbf}html>body:not([is-loading])>div.tableContainer>table>tbody>tr:not(.parent):hover{background-color:rgba(23,26,40,.55)}html>body:not(.compact)>div.bottom div:nth-child(2)>span{max-width:20vw}html>body.compact>div.bottom div:nth-child(2)>span{max-width:300px}html>body>div.focusOverlay{-webkit-backdrop-filter:blur(1px);backdrop-filter:blur(1px);background-color:rgba(0,0,0,.5);bottom:0;left:0;overflow:hidden;position:fixed;right:0;top:0;z-index:3}html>body select:not(.default){-webkit-appearance:none;-moz-appearance:none;appearance:none;background-color:#20222d;border:1px solid #2c2e39;border-radius:2px;box-shadow:0 1px 0 1px rgba(0,0,0,.1);box-sizing:border-box;color:#fff;display:block;line-height:1.3;margin:0;max-width:100%;padding:2px 4px;width:100%}@media only screen and (min-width:768px){html>body.compact{border:1px solid #19191d;margin:0 auto 20px;max-width:960px;min-width:768px;width:auto}}@media only screen and (max-width:768px){html>body>div.rootGallery>div.galleryBar{font-size:.8em!important}html>body>div.rootGallery>div.galleryContent .media .loader{font-size:.8em!important;padding:4px 7px!important}html>body>div.rootGallery>div.galleryContent .screenNavigate{background-color:rgba(0,0,0,.25);cursor:pointer;display:table!important;font-weight:700;height:100%;padding:0 1.2vw;position:absolute;z-index:1}html>body>div.rootGallery>div.galleryContent .screenNavigate.navigateLeft{left:0}html>body>div.rootGallery>div.galleryContent .screenNavigate.navigateLeft>span:after{content:"←"}html>body>div.rootGallery>div.galleryContent .screenNavigate.navigateRight{right:0}html>body>div.rootGallery>div.galleryContent .screenNavigate.navigateRight>span:after{content:"→"}html>body>div.rootGallery>div.galleryContent .screenNavigate>span{display:table-cell;vertical-align:middle}html>body>div.rootGallery>div.galleryContent .media>div.item-info-static{right:25px!important}html>body>div.rootGallery>div.galleryContent .media .wrapper .cover .reverse{font-size:9px;z-index:2}html>body>div.settingsContainer{left:20px}html>body [data-view=mobile]{display:unset!important}html>body [data-view=desktop]{display:none!important}}html>body [data-view=mobile]{display:none}html>body [data-view=desktop]{display:unset}@media only screen and (max-width:768px){body{font-size:10px!important;font-size:11px!important;padding:31px 2px 2px}body>div.tableContainer>table{font-size:10px!important}body>div.tableContainer>table>tbody>tr>td,body>div.tableContainer>table>thead>tr>th{padding:5px 0!important}body>div.tableContainer>table span.sortingIndicator{margin-left:4px!important}body>div.bottom div:first-child:after{content:""!important;margin:none!important}body>div.top{font-size:11px!important}body>div.path{font-size:14px!important}body>div.bottom{font-size:10px!important;margin-bottom:2px;padding-top:0;text-align:left}body>div.bottom div:first-child,body>div.bottom div:nth-child(2){display:block!important;margin-top:9px}body>div.bottom div:before{content:""!important}body>div.topBar>div.extend{padding:0 12px}div.referenceGit{margin-top:14px;position:unset!important;right:unset!important;text-align:left!important}div.topBar>div.directoryInfo>div[data-count=directories]{width:100%}.menu>div{padding:10px 18px 10px 7px}}</style>';

/* Alternative stylesheet output for when single-page is enabled */
if($config['single_page'])
{
  /* Check if `navigateType` is set */
  if($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_POST['navigateType']) && $_POST['navigateType'] === 'dynamic')
  {
    /* Set a header to identify the response on the client side */
    header('navigate-type: dynamic');

    $stylePath = $indexer->joinPaths(BASE_PATH, '/indexer/', '/css/style.css');

    if(file_exists($stylePath))
    {
      $styleData = file_get_contents($stylePath);

      /* If any additional CSS is set, merge that with this output */
      if(!empty($additionalCss))
      {
        $styleData .= (' ' . $additionalCss);
        $additionalCss = '';
      }

      $baseStylesheet = sprintf('<style type="text/css">%s</style>' . PHP_EOL, $styleData);
    }
  }
}

/* Passed to any inject functions that are called from config */
$injectPassableData = array();

if($config['inject'])
{
  /* Current path */
  $injectPassableData['path'] = $indexer->getCurrentDirectory();
  /* Get file and directory counts */
  $injectPassableData['counts'] = $counts;
  /* Get directory size */
  $injectPassableData['size'] = $data['size'];
  /* Pass config values */
  $injectPassableData['config'] = $config;
}

/* Gets the inject options */
$getInjectable = function($key) use ($config, $injectPassableData)
{
  if($config['inject'] && array_key_exists($key, $config['inject']))
  {
    if($config['inject'][$key])
    {
      if(is_string($config['inject'][$key]))
      {
        return $config['inject'][$key] . PHP_EOL;
      } else if(is_callable($config['inject'][$key]))
      {
        return $config['inject'][$key]($injectPassableData) . PHP_EOL;
      }
    }
    return PHP_EOL;
  } else {
    return PHP_EOL;
  }
}
?>
<!DOCTYPE HTML>
<html lang="en">
  <head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?=sprintf($config['format']['title'], $indexer->getCurrentDirectory());?></title>
    <link rel="shortcut icon" href="<?=$config['icon']['path'];?>" type="<?=$config['icon']['mime'];?>">
    <?=$baseStylesheet;?>
    <?=($currentTheme && strtolower($currentTheme) !== 'default' && isset($themes[$currentTheme]))
      ? PHP_EOL . '    <link rel="stylesheet" type="text/css" href="' . $themes[$currentTheme]['path'] . '?bust=' . $bust . '">' . PHP_EOL
      : ''?>
    
    <?=!(empty($additionalCss)) ? sprintf('<style type="text/css">%s</style>' . PHP_EOL, $additionalCss) : PHP_EOL?>
    <?=$getInjectable('head');?>
  </head>

  <body class="rootDirectory<?=$compact ? ' compact' : ''?><?=!$footer['enabled'] ? ' pb' : ''?>" is-loading<?=$config['performance'] ? ' optimize' : '';?> root>
    <?=$getInjectable('body');?>

    <div class="topBar">
        <div class="extend">&#9881;</div>
        <div class="directoryInfo">
          <div data-count="size"><?=$data['size']['readable'];?></div>
          <div <?=$data['recent']['file'] !== 0 ? 'data-raw="' . $data['recent']['file'] . '" ' : '';?>data-count="files"><?=$counts['files'] . ($counts['files'] === 1 ? ' file' : ' files');?></div>
          <div <?=$data['recent']['directory'] !== 0 ? 'data-raw="' . $data['recent']['directory'] . '" ' : '';?>data-count="directories"><?=$counts['directories'] . ($counts['directories'] === 1 ? ' directory' : ' directories');?></div>
        </div>
    </div>

    <div class="path">XENIUM UwU<?=$indexer->makePathClickable($indexer->getCurrentDirectory());?></div>
    

    <div class="tableContainer">
      <table>
      <thead>
        <tr>
          <th><span sortable="true" title="Sort by filename">Filename</span><span class="sortingIndicator"></span></th>
          <th><span sortable="true" title="Sort by modification date">Modified</span><span class="sortingIndicator"></span></th>
          <th><span sortable="true" title="Sort by filesize">Size</span><span class="sortingIndicator"></span></th>
          <th><span sortable="true" title="Sort by filetype">Type</span><span class="sortingIndicator"></span></th>
        </tr>
      </thead>

      <?=$contents;?>

      </table>
    </div>
<?php
if($footer['enabled'])
{
  echo '<div class="bottom">';

  echo sprintf(
    '  <div class="%s">Page generated in <span class="%s">%f</span> seconds</div><div>Browsing <span>%s</span>%s</div>',
    'currentPageInfo',
    'generationTime',
    microtime(true) - $render,
    $indexer->getCurrentDirectory(),
    $footer['show_server_name'] && !empty($_SERVER['SERVER_NAME']) ? sprintf(' @ <a href="/">%s</a>', $_SERVER['SERVER_NAME']) : ''
  );

  echo ($config['credits'] !== false) ? sprintf(
    '<div class="referenceGit">
    <a target="_blank" href="https://xenium.to/">HOSTED ON AN FRITZBOX</a><span>%s</span>
  </div>', $version
  ) : '';

  echo '</div>';
}
?>

<div class="filterContainer" style="display: none;">
    <input type="text" placeholder="Search .." value="">
</div>

 

<script id="__IVFI_DATA__" type="application/json"><?=(json_encode(array(
  'bust' => $bust,
  'singlePage' => $config['single_page'],
  'preview' => array(
    'enabled' => $config['preview']['enabled'],
    'hoverDelay' => $config['preview']['hover_delay'],
    'cursorIndicator' => $config['preview']['cursor_indicator'],
  ),
  'sorting' => array(
    'enabled' => $sorting['enabled'],
    'types' => $sorting['types'],
    'sortBy' => strtolower($sorting['sort_by']),
    'order' => $sorting['order'] === SORT_ASC ? 'asc' : 'desc',
    'directorySizes' => $config['directory_sizes']['enabled']
  ),
  'gallery' => array(
    'enabled' => $config['gallery']['enabled'],
    'reverseOptions' => $config['gallery']['reverse_options'],
    'scrollInterval' => $config['gallery']['scroll_interval'],
    'listAlignment' => $config['gallery']['list_alignment'],
    'fitContent' => $config['gallery']['fit_content'],
    'imageSharpen' => $config['gallery']['image_sharpen']
  ),
  'extensions' => array(
    'image' => $config['extensions']['image'],
    'video' => $config['extensions']['video']
  ),
  'style' => array(
    'themes' => array(
      'path' => $config['style']['themes']['path'],
      'pool' => $themes,
      'set' => $currentTheme ? $currentTheme : 'default'
    ),
    'compact' => $config['style']['compact']
  ),
  'format' => array_intersect_key($config['format'], array_flip(array('sizes', 'date', 'title'))),
  'encodeAll' => $config['encode_all'],
  'performance' => $config['performance'],
  'timestamp' => $indexer->timestamp,
  'debug' => $config['debug'],
  'mobile' => false
  )));?>
</script>

<script type="text/javascript">function getScrollbarWidth(){const e=document.createElement("div");e.style.visibility="hidden",e.style.overflow="scroll",e.style.msOverflowStyle="scrollbar",document.body.appendChild(e);const t=document.createElement("div");e.appendChild(t);const l=e.offsetWidth-t.offsetWidth;return e.parentNode.removeChild(e),l};document.documentElement.style.setProperty('--scrollbar-width', getScrollbarWidth() + 'px');</script>
<script type="text/javascript">


 
(()=>{var e={6808:(e,t,n)=>{var o,i;
/*!
 * JavaScript Cookie v2.2.1
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */!function(r){if(void 0===(i="function"==typeof(o=r)?o.call(t,n,t,e):o)||(e.exports=i),!0,e.exports=r(),!!0){var a=window.Cookies,s=window.Cookies=r();s.noConflict=function(){return window.Cookies=a,s}}}((function(){function e(){for(var e=0,t={};e<arguments.length;e++){var n=arguments[e];for(var o in n)t[o]=n[o]}return t}function t(e){return e.replace(/(%[0-9A-Z]{2})+/g,decodeURIComponent)}return function n(o){function i(){}function r(t,n,r){if("undefined"!=typeof document){"number"==typeof(r=e({path:"/"},i.defaults,r)).expires&&(r.expires=new Date(1*new Date+864e5*r.expires)),r.expires=r.expires?r.expires.toUTCString():"";try{var a=JSON.stringify(n);/^[\{\[]/.test(a)&&(n=a)}catch(e){}n=o.write?o.write(n,t):encodeURIComponent(String(n)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),t=encodeURIComponent(String(t)).replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent).replace(/[\(\)]/g,escape);var s="";for(var l in r)r[l]&&(s+="; "+l,!0!==r[l]&&(s+="="+r[l].split(";")[0]));return document.cookie=t+"="+n+s}}function a(e,n){if("undefined"!=typeof document){for(var i={},r=document.cookie?document.cookie.split("; "):[],a=0;a<r.length;a++){var s=r[a].split("="),l=s.slice(1).join("=");n||'"'!==l.charAt(0)||(l=l.slice(1,-1));try{var c=t(s[0]);if(l=(o.read||o)(l,c)||t(l),n)try{l=JSON.parse(l)}catch(e){}if(i[c]=l,e===c)break}catch(e){}}return e?i[e]:i}}return i.set=r,i.get=function(e){return a(e,!1)},i.getJSON=function(e){return a(e,!0)},i.remove=function(t,n){r(t,"",e(n,{expires:-1}))},i.defaults={},i.withConverter=n,i}((function(){}))}))},1007:(e,t,n)=>{"use strict";n.r(t)},9731:(e,t,n)=>{"use strict";n.r(t)},3858:(e,t,n)=>{"use strict";n.r(t)},7975:(e,t,n)=>{"use strict";n.r(t)},2333:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0;var i=o(n(6808)),r=o(n(6866)),a=o(n(823)),s=n(8598),l=n(9879),c=n(6424),u=o(n(152)),d=n(3987);n(9731);var p=n(6931),f=function(e,t){void 0===t&&(t={});var n=this;this.setDefaults=function(){var e={extensions:{image:["jpg","jpeg","gif","png","ico","svg","bmp","webp"],video:["mp4","webm","ogg","ogv","mov"]},list:{show:!0,reverse:!1},video:{video:null},performance:!1,autoplay:!0,volume:0,console:!0,reverseOptions:!0,sharpen:!0,mobile:!1,fitContent:!1,encodeAll:!1,scrollInterval:35,start:0,listAlignment:0};return n.defaults=e,n.defaults},this.init=function(e){if(n.data={},n.data.busy=!1,n.data.boundEvents={},n.data.scrollbreak=!1,n.data.keyPrevent=[p.Keys.pageUp,p.Keys.pageDown,p.Keys.arrowLeft,p.Keys.arrowUp,p.Keys.arrowRight,p.Keys.arrowDown],n.data.selected={src:null,ext:null,index:null,type:null},n.container=document.body.querySelector(":scope > div.rootGallery"),n.items=n.options.filter?n.filterItems(e):e,0===n.items.length)return!1;n.exists()?n.show(!0):n.initiate((function(){n.bind()}));var t=n.options.start>n.items.length-1?n.items.length-1:n.options.start;n.navigate(t),n.options.performance&&n.useOptimzer(n.table),n.options.list.show&&!n.options.mobile||(n.list.style.display="none")},this.loadImage=function(e){return c.eventHooks.unsubscribe("galleryItemChanged","loadImage"),new Promise((function(t,n){var o=document.createElement("img"),i=function(){n(new Error("failed to load image URL: ".concat(e)))},r=function(){var n={width:o.naturalWidth,height:o.naturalHeight};t({src:e,img:o,dimensions:n,cancelled:!1})};o.src=e,o.addEventListener("error",i,!0),o.addEventListener("load",r,!0),c.eventHooks.subscribe("galleryItemChanged","loadImage",(function(n){n.source!==e&&(o.removeEventListener("error",i,!0),o.removeEventListener("load",r,!0),o.src="",o=null,c.eventHooks.unsubscribe("galleryItemChanged","loadImage")),t({src:e,img:null,dimensions:null,cancelled:!0})}))}))},this.elementHasScrollbar=function(e){var t=e.getBoundingClientRect().height,n=window.getComputedStyle(e);return(t=["top","bottom"].map((function(e){return parseInt(n["margin-"+e],10)})).reduce((function(e,t){return e+t}),t))>window.innerHeight},this.encodeUrl=function(e){var t=n.options.encodeAll?e:encodeURI(e);return n.options.encodeAll&&(t=t.replace("#","%23").replace("?","%3F")),t=t.replace("+","%2B")},this.getExtension=function(e){return e.split(".").pop().toLowerCase()},this.isImage=function(e,t){return void 0===t&&(t=null),n.options.extensions.image.includes(t||n.getExtension(e))},this.isVideo=function(e,t){return void 0===t&&(t=null),n.options.extensions.video.includes(t||n.getExtension(e))},this.filterItems=function(e){return e.filter((function(e){return n.isImage(e.name)||n.isVideo(e.name)}))},this.getScrollbarWidth=function(){if(!n.elementHasScrollbar(document.body))return 0;var e=document.createElement("div");d.DOM.style.set(e,{visibility:"hidden",overflow:"scroll",msOverflowStyle:"scrollbar"}),document.body.appendChild(e);var t=document.createElement("div");e.appendChild(t);var o=e.offsetWidth-t.offsetWidth;return e.parentNode.removeChild(e),o},this.limitBody=function(e){void 0===e&&(e=!0);var t=document.body,o=document.documentElement,i=n.getScrollbarWidth();!0===e?(document.documentElement.setAttribute("gallery-is-visible",""),n.isVisible=!0,n.data.body={"max-height":t.style["max-height"],overflow:t.style.overflow},i>0&&d.DOM.style.set(o,{"padding-right":i+"px"}),d.DOM.style.set(t,{"max-height":"calc(100vh - var(--height-gallery-top-bar))",overflow:"hidden"})):(document.documentElement.removeAttribute("gallery-is-visible"),n.isVisible=!1,Object.prototype.hasOwnProperty.call(n.data,"body")&&d.DOM.style.set(t,{"max-height":n.data.body["max-height"],overflow:n.data.body.overflow}),d.DOM.style.set(o,{"padding-right":"unset"}))},this.exists=function(){return n.container=document.body.querySelector(":scope > div.rootGallery"),!!n.container},this.show=function(e,t,o){void 0===e&&(e=!0),void 0===t&&(t=null),void 0===o&&(o=null),o&&((0,l.log)("gallery","itemsUpdate",!0),n.data.selected.index=null,n.items=n.options.filter?n.filterItems(o):o,n.populateTable(n.items)),!0===e?(n.bind().style.display="block",t!==n.data.selected.index&&(n.container.querySelectorAll(":scope > div.galleryContent > div.media > div.wrapper img, \t\t\t\t\t:scope > div.galleryContent > div.media > div.wrapper video").forEach((function(e){e.style.display="none"})),n.navigate(t),o&&n.options.performance&&n.useOptimzer(n.table))):(n.unbind(),n.container.style.display="none"),n.limitBody(e);var i=n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper video");if(i)if(!0===e&&"none"!==i.style.display){var r=i.currentTime,a=!1;n.options.continue.video&&Object.prototype.hasOwnProperty.call(n.options.continue.video,"src")&&(a=i.querySelector("source").getAttribute("src")===n.options.continue.video.src),n.options.continue.video&&a&&(r=n.options.continue.video.time,n.options.continue.video=null),i.currentTime=r,i.muted=!1,i[n.options.autoplay?"play":"pause"](),n.video.setVolume(i,n.video.getVolume())}else!1===e&&i.pause();if(e&&n.options.performance&&n.optimize&&n.list&&n.table){var s=n.table.querySelector("tr.selected"),c=parseInt(s.style.top.replace(/\D+/g,""));!Number.isInteger(c)||c>=0||(c=!1),c&&(n.list.scrollTop<=c&&c<=n.list.scrollTop+n.list.offsetHeight||n.list.scrollTo(0,c)),n.optimize.attemptRefresh()}},this.busy=function(e){if(!0===e||!1===e){n.data.busy=e;var t=n.container.querySelector(":scope > div.galleryContent > div.media > div.spinner");e?d.DOM.style.set(t,{opacity:"1"}):t.style.opacity="0"}return n.data.busy},this.useOptimzer=function(e){n.optimize&&(delete n.optimize,c.eventHooks.unlisten(n.list,"scroll","galleryTableScroll"),d.DOM.style.set(n.table,{height:"auto"}));var t={update:function(){return t.windowHeight=window.innerHeight,t.windowWidth=window.innerWidth,t.scrolledY=window.scrollY,!0},scope:e};a.default.layer.gallery=t,t.update=function(){return t.windowHeight=window.innerHeight,t.windowWidth=window.innerWidth,t.scrolledY=n.list.scrollTop,!0},t.update(),n.page=t,n.optimize=new u.default({page:t,table:e,scope:[n.list,"scrollTop"]}),c.eventHooks.unlisten(window,"resize","windowGalleryResize"),c.eventHooks.listen(window,"resize","windowGalleryResize",(0,d.debounce)((function(){n.options.performance&&n.optimize.enabled&&((0,l.log)("gallery","windowResize (gallery)","Resized."),t.update())})));var o=null;return c.eventHooks.listen(n.list,"scroll","galleryTableScroll",(function(){if(n.options.performance&&n.optimize.enabled){var e=n.list.scrollTop;Math.abs(e-n.page.scrolledY)>175&&n.optimize.attemptRefresh(),clearTimeout(o),o=window.setTimeout((function(){n.optimize.attemptRefresh()}),150)}})),n.optimize},this.populateTable=function(e,t){(0,l.log)("gallery","Populating gallery list .."),t=t||n.container.querySelector("div.galleryContent > div.list > table");for(var o=[],i=0;i<=e.length-1;i++)o[i]='<tr title="'.concat(e[i].name,'"><td>').concat(e[i].name,"</td></tr>");return t.innerHTML=o.join(""),n.list=n.container.querySelector("div.galleryContent > div.list"),n.table=t,t},this.update={listWidth:function(e){e=e||n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper");var t=n.data.list?n.data.list:n.container.querySelector(":scope > div.galleryContent > div.list"),o=n.options.mobile||!t||"none"===t.style.display?0:t.offsetWidth;e.style.setProperty("--width-list","".concat(o,"px"))}},this.getReverseOptions=function(e){e=n.encodeUrl(document.location.origin+e);var t={};return Object.keys(a.default.text.reverseSearch).forEach((function(n){t[n]=a.default.text.reverseSearch[n].replace("{URL}",e)})),t},this.reverse=function(e){if(!n.options.reverseOptions)return!1;var t=n.container.querySelector(":scope > div.galleryContent > div.media .reverse");if(!t){var o=d.DOM.new("div",{class:"reverse"});e.prepend(o),t=o}var i=n.getReverseOptions(n.data.selected.src);t.innerHTML=Object.keys(i).map((function(e){return'<a class="reverse-link" target="_blank" href="'.concat(i[e],'">').concat(e,"</a>")})).join(""),n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper > div.cover").append(t)},this.apply={cache:{info:null},timers:{dimensions:null},itemDimensions:function(e){var t=n.items[e],o=n.container.querySelector("div.media > div.item-info-static");Object.prototype.hasOwnProperty.call(t,"dimensions")&&t.dimensions.height>0&&t.dimensions.width>0?(o||(o=d.DOM.new("div",{class:"item-info-static"}),n.container.querySelector("div.media").appendChild(o)),o.style.opacity="1",o.style.display="inline-block",o.textContent="".concat(t.dimensions.width," x ").concat(t.dimensions.height," (").concat(t.size,")")):o&&(o.style.display="none"),clearTimeout(n.apply.timers.dimensions),n.apply.timers.dimensions=setTimeout((function(){o&&(o.style.opacity="0")}),3e3)},itemInfo:function(e,t,o,i){var r;if(void 0===t&&(t=null),void 0===o&&(o=null),void 0===i&&(i=null),!e)return n.apply.cache.info=[t,o,i],!1;if(Array.isArray(n.apply.cache.info))t=(r=n.apply.cache.info)[0],o=r[1],i=r[2];else if(null===t||null===o||null===i)return!1;var a=n.container.querySelector(".galleryBar > .galleryBarRight > a.download"),s=n.container.querySelector(":scope > div.galleryBar > div.galleryBarLeft"),l=n.options.mobile?(0,d.shortenString)(t.name,30):t.name,c=n.encodeUrl(t.url);d.DOM.attributes.set(a,{filename:t.name,href:c,title:"Download: ".concat(t.name)});var u=["<span>".concat(o+1," of ").concat(i,"</span>"),'<a target="_blank" href="'.concat(c,'">').concat(l,"</a>")];return Object.prototype.hasOwnProperty.call(t,"size")&&!n.options.mobile&&u.push("<span>".concat(t.size,"</span>")),s.innerHTML=u.join(""),!0}},this.isScrolledIntoView=function(e,t){var n={scrolled:e.scrollTop,height:e.offsetHeight},o={offset:t.offsetTop,height:t.children[0].offsetHeight};return(0,l.log)("gallery","isScrolledIntoView",n,o),o.offset>=n.scrolled&&o.offset+o.height<=n.scrolled+n.height},this.calculateIndex=function(e,t,o){var i=e+t;return i>o&&(i=i-o-1),i<0&&(i=o-(Math.abs(i)-1)),i<0||i>o?n.calculateIndex(e,o-i,o):i},this.video={create:function(e){var t=d.DOM.new("video",{controls:"",preload:"auto",loop:""}),o=d.DOM.new("source",{type:"video/".concat("mov"===e?"mp4":"ogv"===e?"ogg":e),src:""});return t.append(o),n.video.setVolume(t,n.video.getVolume()),[t,o]},getVolume:function(){var e=parseFloat(n.options.volume.toString());return e=isNaN(e)||e<0||e>1?0:e},setVolume:function(e,t){return t>0?e.volume=t>=1?1:t:e.muted=!0,t},seek:function(e){var t=n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper video");if(t){var o=Math.round(t.currentTime),i=Math.round(t.duration);if(e>0){if(o+e>i)return!0;t.currentTime=o+e}else if(e<0){if(o+e<0)return!0;t.currentTime=o+e}return!1}}},this.showItem=function(e,t,o,i,r,a){void 0===a&&(a=null),(0,l.log)("gallery","showItem",{type:e,element:t,src:o,init:i,index:r,data:a});var s,u=n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper"),p=null,f=!1,v=function(){var t=u.querySelector(0===e?"video":"img");t&&1===e&&(t.closest(".cover").style.display="none"),t&&(t.style.display="none")},h=function(t){n.container.querySelectorAll(":scope > \t\t\t\tdiv.galleryContent > div.media > div.wrapper > div:not(.cover)").forEach((function(e){return e.remove()})),n.apply.itemInfo(!0),n.data.selected.type=e,u.style.display="",t&&t(),n.busy(!1)};!function(){var l;if(0===e)s=u.querySelector("video"),n.items[r].dimensions={height:a.img.height,width:a.img.width},n.apply.itemDimensions(r),h((function(){if(n.options.sharpen&&t.setAttribute("sharpened",""),t.onload=function(){if(v(),n.options.fitContent){var e="calc(calc(100vw - var(--width-list)) / ".concat((a.img.width/a.img.height).toFixed(4),")");n.update.listWidth(u),d.DOM.style.set(t,{width:"auto",height:e}),d.DOM.style.set(t.closest(".cover"),{height:e})}},t.setAttribute("src",o),t.style.display="inline-block",t.closest(".cover").style.display="",s){var e=s.querySelector("source");s.pause(),e.setAttribute("src",""),[[s,"videoError"],[e,"sourceError"]].forEach((function(e){var t=e[0],n=e[1];c.eventHooks.unlisten(t,"error",n)}))}}));else if(1===e){!1===i?(l=n.video.create(n.data.selected.ext),s=l[0],p=l[1],u.append(s)):(p=t.querySelector("source"),s=t),p.setAttribute("src",o),s.srcId=o,[[s,"videoError"],[p,"sourceError"]].forEach((function(e){var t=e[0],o=e[1];c.eventHooks.listen(t,"error",o,(function(e){!function(e){console.error("Failed to load video source.",e),n.busy(!1),s.remove()}(e)}))})),c.eventHooks.listen(s,"volumechange","galleryVideoVolumeChange",(function(){n.options.volume=s.muted?0:parseFloat(parseFloat(s.volume).toFixed(2)),c.eventHooks.trigger("galleryVolumeChange",n.options.volume)}));var y=["canplay","canplaythrough","playing"];c.eventHooks.subscribe("galleryItemChanged","loadVideo",(function(){s.srcId!==n.data.selected.src&&(p.setAttribute("src",""),c.eventHooks.unlisten(s,y,"awaitGalleryVideo"),c.eventHooks.unsubscribe("galleryItemChanged","loadVideo"),s.classList.add("disposable"))})),c.eventHooks.listen(s,y,"awaitGalleryVideo",(function(){if(f||s.srcId!==n.data.selected.src)return!1;var e=s.videoHeight,o=s.videoWidth;n.items[r].dimensions={height:e,width:o},n.apply.itemDimensions(r),h((function(){n.options.fitContent&&(n.update.listWidth(u),d.DOM.style.set(s,{width:"auto",height:"calc(calc(100vw - var(--width-list)) / ".concat((o/e).toFixed(4),")")})),n.options.volume&&(s.volume=n.options.volume),n.isVisible&&n.options.autoplay?s.play():n.isVisible||s.pause(),n.container.querySelectorAll("video.disposable").forEach((function(e){return e.remove()})),v(),s.style.display="inline-block","none"===n.container.style.display&&(n.container.querySelector("div.galleryContent .media div.spinner").style.opacity="0",s.pause()),!1===i&&t.remove(),f=!0}))}),{destroy:!0}),n.options.continue.video&&o==n.options.continue.video.src&&(s.currentTime=n.options.continue.video.time,n.options.continue.video=null)}n.data.selected.index=r}()},this.navigate=function(e,t){void 0===t&&(t=null),(0,l.log)("gallery","busyState",n.busy());var o=n.items.length-1;if(null===e&&(e=n.data.selected.index),null!==t&&(e=n.calculateIndex(e,t,o)),n.data.selected.index===e)return!1;var i,r=null,a=n.container.querySelector(":scope > div.galleryContent"),s=a.querySelector(":scope > div.media > div.wrapper img"),u=a.querySelector(":scope > div.media > div.wrapper video"),p=a.querySelector(":scope > div.list"),f=p.querySelector("table"),v=f.querySelector("tr:nth-child(".concat(e+1,")"));i=n.items[e];var h=n.encodeUrl(i.url);n.data.selected.src=h,n.data.selected.ext=n.getExtension(i.name),f.querySelector("tr.selected")&&f.querySelector("tr.selected").classList.remove("selected"),v.classList.add("selected"),n.apply.itemInfo(!s&&!u,i,e,o+1);var y=!1;if(n.options.performance&&n.optimize&&n.optimize.enabled&&v.classList.contains("hid-row")&&v._offsetTop>=0){var g=v._offsetTop-p.offsetHeight/2;p.scrollTo(0,g>=0?g:0),y=!0}if(y||n.isScrolledIntoView(p,v)||p.scrollTo(0,v.offsetTop),c.eventHooks.trigger("galleryItemChanged",{source:h,index:e}),n.isImage(null,n.data.selected.ext)){if(n.busy(!0),r=!s,u&&u.pause(),!0===r){var m=d.DOM.new("div",{class:"cover",style:"display: none"}),b=n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper");s=d.DOM.new("img"),b.prepend(m),m.append(s),m.addEventListener("mouseenter",(function(e){n.options.reverseOptions&&n.reverse(e.currentTarget)}))}return n.loadImage(h).then((function(t){var o=t.src,i=t.dimensions,a=t.cancelled;if(i&&!a){var l=[i.width,i.height],c=l[0],u=l[1];n.data.selected.src===o&&n.showItem(0,s,o,r,e,{img:{width:c,height:u}})}})).catch((function(t){console.error(t),n.busy(!1),n.data.selected.index=e,n.container.querySelectorAll(":scope > div.galleryContent > div.media > div.wrapper img, \t\t\t\t\t:scope > div.galleryContent > div.media > div.wrapper video").forEach((function(e){e.style.display="none"})),n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper > div:not(.cover)")&&n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper > div:not(.cover)").remove();var o=d.DOM.new("div",{class:"error"});o.innerHTML="Error: Image could not be displayed.",n.container.querySelector(".media .wrapper").append(o)})),!0}return n.isVideo(null,n.data.selected.ext)?(n.busy(!0),(r=!u)&&(u=n.video.create(n.data.selected.ext)[0],n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper").append(u)),n.showItem(1,u,h,r,e),!0):void 0},this.handleKey=function(e,t){(0,l.log)("gallery","handleKey",e),e===p.Keys.escape?n.show(!1):e===p.Keys.arrowDown||e===p.Keys.pageDown||e===p.Keys.arrowRight?e===p.Keys.arrowRight&&1===n.data.selected.type?n.video.seek(5)&&n.navigate(null,1):n.navigate(null,1):e===p.Keys.arrowUp||e===p.Keys.pageUp||e===p.Keys.arrowLeft?e===p.Keys.arrowLeft&&1===n.data.selected.type?n.video.seek(-5)&&n.navigate(null,-1):n.navigate(null,-1):e===p.Keys.l&&n.toggleList(),t(n.data.keyPrevent.includes(e))},this.removeOnUnbind=function(e,t,o){n.data.boundEvents[o]={selector:e,events:t}},this.unbind=function(){Object.keys(n.data.boundEvents).forEach((function(e){var t=n.data.boundEvents[e],o=t.selector,i=t.events;c.eventHooks.unlisten(o,i,e)})),n.data.boundEvents={},c.eventHooks.trigger("galleryUnbound")},this.scrollBreak=function(){n.data.scrollbreak=!1},this.toggleList=function(e){void 0===e&&(e=null);var t=n.container.querySelector(":scope > div.galleryContent > div.list"),o="none"!==t.style.display,i=s.user.get();return i.gallery.listState=o?0:1,s.user.set(i),e||(e=document.body.querySelector('div.rootGallery > div.galleryBar .galleryBarRight span[data-action="toggle"]')),e.innerHTML='List<span class="inheritParentAction">'.concat(o?"+":"-","</span>"),d.DOM.style.set(t,{display:o?"none":"table-cell"}),n.update.listWidth(),!o&&n.options.performance&&n.optimize.enabled&&n.optimize.attemptRefresh(),!o},this.bind=function(){return n.unbind(),c.eventHooks.listen(n.data.listDrag,"mousedown","galleryListMouseDown",(function(){n.data.listDragged=!0;var e=window.innerWidth,t=n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper");d.DOM.style.set(document.body,{cursor:"w-resize"}),d.DOM.style.set(t,{"pointer-events":"none"}),n.list&&d.DOM.style.set(n.list,{"pointer-events":"none"}),n.data.listDrag&&n.data.listDrag.setAttribute("dragged","true"),c.eventHooks.listen("body > div.rootGallery","mousemove","galleryListMouseMove",(function(t){var o=t.clientX;if(o<e){var i=n.options.list.reverse?o+n.getScrollbarWidth():e-o;requestAnimationFrame((function(){d.DOM.style.set(n.data.list,{width:"".concat(i,"px")})}))}}),{onAdd:n.removeOnUnbind})}),{onAdd:n.removeOnUnbind}),c.eventHooks.listen("body > div.rootGallery","mouseup","galleryListMouseUp",(function(){if(!0===n.data.listDragged){c.eventHooks.unlisten("body > div.rootGallery","mousemove","galleryListMouseMove");var e=n.container.querySelector(":scope > div.galleryContent > div.media > div.wrapper");d.DOM.style.set(document.body,{cursor:""}),d.DOM.style.set(e,{"pointer-events":"auto"}),n.list&&d.DOM.style.set(n.list,{"pointer-events":"auto"}),n.data.listDrag&&n.data.listDrag.removeAttribute("dragged");var t=parseInt(n.data.list.style.width.replace(/[^-\d.]/g,""));if((0,l.log)("gallery","Set list width",t),t>100){var o=JSON.parse(i.default.get(p.CookieKey));o.gallery.listWidth=t,i.default.set(p.CookieKey,JSON.stringify(o),{sameSite:"lax",expires:365}),n.update.listWidth(e)}n.data.listDragged=!1}}),{onAdd:n.removeOnUnbind}),c.eventHooks.listen("body > div.rootGallery","click","galleryContainerClick",(function(e){var t=e.target;if(t&&"SPAN"===t.tagName&&t.classList.contains("inheritParentAction")&&(t=t.parentNode),t&&t.hasAttribute("data-action")){var o=t.getAttribute("data-action").toLowerCase(),i={next:function(){n.navigate(null,1)},previous:function(){n.navigate(null,-1)},toggle:function(){n.toggleList(t)},close:function(){n.show(!1)}};i[o]&&i[o]()}}),{onAdd:n.removeOnUnbind}),c.eventHooks.listen("body > div.rootGallery > div.galleryContent \t\t\t> div.list table","click","listNavigateClick",(function(e){"TD"===e.target.tagName?n.navigate(d.DOM.getIndex(e.target.closest("tr"))):"TR"===e.target.tagName&&n.navigate(d.DOM.getIndex(e.target))}),{onAdd:n.removeOnUnbind}),c.eventHooks.listen("body > div.rootGallery > div.galleryContent \t\t\t> div.media","click","mediaClick",(function(e){["IMG","VIDEO","A"].includes(e.target.tagName)||n.show(!1)}),{onAdd:n.removeOnUnbind}),!0===n.options.mobile&&new r.default({element:document.querySelector("body > div.rootGallery"),onSwiped:function(e,t){if(t.absX>=t.absY)switch(t.directionX){case"RIGHT":n.navigate(null,-1);break;case"LEFT":n.navigate(null,1)}else switch(t.directionY){case"TOP":n.navigate(null,-1);break;case"BOTTOM":n.navigate(null,1)}},mouseTrackingEnabled:!0}).init(),c.eventHooks.listen("body > div.rootGallery  > div.galleryContent > \t\t\tdiv.media",["scroll","DOMMouseScroll","mousewheel"],"galleryScrollNavigate",(function(e){if(n.options.scrollInterval>0&&!0===n.data.scrollbreak)return!1;n.navigate(null,e.detail>0||e.deltaY>0?1:-1),n.options.scrollInterval>0&&(n.data.scrollbreak=!0,setTimeout((function(){return n.scrollBreak()}),n.options.scrollInterval))}),{onAdd:n.removeOnUnbind}),c.eventHooks.listen(window,"keyup","galleryKeyUp",(function(e){n.handleKey(e.code,(function(t){t&&e.preventDefault()}))}),{onAdd:n.removeOnUnbind}),c.eventHooks.listen(window,"keydown","galleryKeyDown",(function(e){n.data.keyPrevent.includes(e.code)&&e.preventDefault(),e.code===p.Keys.g&&n.show(!1)}),{onAdd:n.removeOnUnbind}),c.eventHooks.trigger("galleryBound",!0),n.container},this.barConstruct=function(e){if(e.append(d.DOM.new("a",{text:n.options.mobile?"Save":"Download",class:"download",download:""})),!n.options.mobile){e.append(d.DOM.new("span",{"data-action":"previous",text:"Previous"})),e.append(d.DOM.new("span",{"data-action":"next",text:"Next"}));var t=d.DOM.new("span",{"data-action":"toggle",text:"List"});t.append(d.DOM.new("span",{class:"inheritParentAction",text:n.options.list.show?"-":"+"})),e.append(t)}return e.append(d.DOM.new("span",{"data-action":"close",text:"Close"})),e},this.initiate=function(e){n.limitBody(!0);var t=document.body.querySelector(":scope > div.preview-container");t&&t.remove(),n.container=d.DOM.new("div",{class:"rootGallery"}),document.body.prepend(n.container);var o=d.DOM.new("div",{class:"galleryBar"});n.container.append(o),o.append(d.DOM.new("div",{class:"galleryBarLeft"})),o.append(n.barConstruct(d.DOM.new("div",{class:"galleryBarRight"})));var r=d.DOM.new("div",{class:"galleryContent"+(n.options.list.reverse?" reversed":"")});n.container.append(r);var a=d.DOM.new("div",{class:"media"}),s=d.DOM.new("div",{class:"ns list"});r.append(n.options.list.reverse?s:a),r.append(n.options.list.reverse?a:s),n.data.listDrag=d.DOM.new("div",{class:"drag"}),s.append(n.data.listDrag),n.data.list=s,n.data.listDragged=!1;var l=JSON.parse(i.default.get(p.CookieKey));try{var c=JSON.parse(l.gallery.listWidth.toString().toLowerCase());c&&parseInt(c.toString())>window.innerWidth/2&&(l.gallery.listWidth=Math.floor(window.innerWidth/2),i.default.set(p.CookieKey,JSON.stringify(l),{sameSite:"lax",expires:365})),c&&d.DOM.style.set(n.data.list,{width:"".concat(c,"px")})}catch(e){l.gallery.listWidth=!1,i.default.set(p.CookieKey,JSON.stringify(l),{sameSite:"lax",expires:365})}if(!0===n.options.mobile){var u=d.DOM.new("div",{class:"screenNavigate navigateLeft","data-action":"previous"}),f=d.DOM.new("div",{class:"screenNavigate navigateRight","data-action":"next"});u.append(d.DOM.new("span")),f.append(d.DOM.new("span")),r.append(f,u)}a.append(d.DOM.new("div",{class:"wrapper"+(n.options.fitContent?" fill":"")})),a.append(d.DOM.new("div",{class:"spinner"}));var v=d.DOM.new("table",{cellspacing:"0"});v.append(d.DOM.new("tbody")),s.append(v),n.populateTable(n.items),e(!0)};var o=this.setDefaults();return Object.keys(o).forEach((function(e){Object.prototype.hasOwnProperty.call(t,e)||(t[e]=o[e])})),this.isVisible=null,this.options=t,this.init(e),this};t.default=f},152:(e,t,n)=>{"use strict";t.__esModule=!0;var o=n(3987),i=n(9879),r=function(e){var t=this;return this.init=function(e){t.page=e.page,t.table=e.table,t.scope=e.scope,t.padding=e.padding||0,Object.prototype.hasOwnProperty.call(e,"on")?t.on=e.on:t.on=!1,t.enabled=!1,t.setup()},this.setup=function(){var e;(0,i.log)("optimize","->","optimize.setup");var n=t.table,r=n.querySelectorAll("tbody > tr"),a=r[0].offsetHeight,s=n.offsetHeight,l={};t.tableOffsetBegin=r[0].offsetTop;for(var c=performance.now(),u=0;u<r.length;u++)r[u]._offsetTop=r[u].offsetTop+t.padding,r[u]._offsetHeight=r[u]._offsetHeight||r[u].offsetHeight,r[u]._isVisible=!0;"object"==typeof t.on&&Object.prototype.hasOwnProperty.call(t.on,"rowChange")&&t.on.rowChange(r),t.rows=Array.from(r),(0,i.log)("optimize","Calculated rows in ".concat(performance.now()-c," ms.")),o.DOM.style.set(n,{height:"".concat(s,"px")});for(var d=0;d<r.length;d++)l[r[d]._offsetTop]={index:d};t.structure=l;var p=0,f=n.getBoundingClientRect().top,v=Math.ceil(t.page.scrolledY-(f+t.page.scrolledY)+t.page.windowHeight/2),h=Math.ceil(2*Math.ceil(t.page.windowHeight/a));t.getActiveData(),p=t.scanForClosest(v,1e3,p);var y=t.calculateRange(p,h),g=y[0],m=y[1];for(d=0;d<r.length;d++){var b=r[d],w=["rel-row"];d>=g&&d<=m||w.push("hid-row"),(e=b.classList).add.apply(e,w),b.style.top="".concat(b._offsetTop,"px")}return t.table=n,t.rowHeight=a,t.initiated=!0,t.enabled=!0,t.activeHasChanged=!0,t.attemptRefresh(),t.initiated},this.refactor=function(){var e=performance.now();(0,i.log)("optimize","->","optimize.refactor");for(var n=t.table,o=t.tableOffsetBegin+t.padding,r={},a={},s=0;s<t.rows.length;s++)(w=t.rows[s])._isVisible&&(r[o]={index:s},a[s]=o,o+=w._offsetHeight);o+=t.padding,t.structure=r,t.activeHasChanged=!0,t.page.scrolledY=t.scope[0][t.scope[1]];var l=0,c=n.getBoundingClientRect().top,u=Math.ceil(t.page.scrolledY-(c+t.page.scrolledY)+t.page.windowHeight/2),d=Math.ceil(2*Math.ceil(t.page.windowHeight/t.rowHeight)),p=t.getActiveData(),f=p[0],v=p[1];l=t.scanForClosest(u,1e3,l),l=t.getRelativeIndex(v,l)||l;for(var h=t.calculateRange(l,d),y=h[0],g=h[1],m=0;m<f.length;m++)m>=y&&m<=g&&(f[m].style.display="flex");for(var b=0;b<t.rows.length;b++){var w;(w=t.rows[b])._isVisible?w.style.top="".concat(a[b],"px"):w.style.top="-2500px"}return"object"==typeof t.on&&Object.prototype.hasOwnProperty.call(t.on,"rowChange")&&t.on.rowChange(f),n.style.height="".concat(o+6,"px"),(0,i.log)("optimize","Ran refactor in ".concat(performance.now()-e," ms.")),t.refresh(),!0},this.setVisibleFlag=function(e,n){return e._isVisible=n,t.activeHasChanged=!0,n},this.sortLogic=function(e,t,n){return(0,o.isNumeric)(t.value)&&(0,o.isNumeric)(n.value)?e?t.value-n.value:n.value-t.value:(e?t.value||"":n.value||"").localeCompare(e?n.value||"":t.value||"")},this.sortRows=function(e,n){void 0===e&&(e=0),void 0===n&&(n="asc");for(var o="asc"===n.toLowerCase()?1:0,r=[],a=[],s=[],l=performance.now(),c=0;c<t.rows.length;c++)if(0!==c){var u=t.rows[c],d=u.children[e].getAttribute("data-raw"),p=u.classList.contains("directory");(d&&!p?a:s).push({value:d,index:c})}for([a,s].forEach((function(e){return e.sort((function(e,n){return t.sortLogic(o,e,n)}))})),a.unshift.apply(a,s),c=0;c<a.length;c++)r.push(t.rows[a[c].index]);return r.unshift(t.rows[0]),t.rows=r,t.refactor(),(0,i.log)("optimize","Sorted items in ".concat(performance.now()-l," ms.")),t.rows},this.calculateRange=function(e,t){var n=e-t,o=n<0?n:0;return[n=n<0?0:n,n+2*t+o]},this.getActiveData=function(){if(t.activeHasChanged||!t.activeData){(0,i.log)("optimize","Updating active data ..");for(var e=[],n={},o=0;o<t.rows.length;o++)t.rows[o]._isVisible&&(e.push(t.rows[o]),n[o]=o);t.activeData=[e,n],t.activeHasChanged=!1}return t.activeData},this.scanForClosest=function(e,n,o){void 0===o&&(o=null);for(var i=o,r=0;r<(n||1e3);r++)if(t.structure[e+r]){i=t.structure[e+r].index;break}return i},this.getRelativeIndex=function(e,t){for(var n=Object.keys(e),o=null,i=0;i<n.length;i++)parseInt(n[i])===t&&(o=i);return o},this.setRows=function(e,n,o){for(var r=0,a=0,s=0,l=t.calculateRange(e,o),c=l[0],u=l[1],d={show:[],hide:[]},p=0;p<n.length;p++){var f=n[p];p>=c&&p<=u?(f._isHidden&&(r++,d.show.push(f)),a++):(f._isHidden||(r++,d.hide.push(f)),s++)}return requestAnimationFrame((function(){d.show.forEach((function(e){e.classList.remove("hid-row"),e._isHidden=!1})),d.hide.forEach((function(e){e.classList.add("hid-row"),e._isHidden=!0}))})),(0,i.log)("optimize",{visible:a,hidden:s,updated:r}),{visible:a,hidden:s,updated:r}},this.refresh=function(e){if(void 0===e&&(e=0),!t.initiated)return new Promise((function(e,t){return t("Not initiated.")}));var n=performance.now();(0,i.log)("optimize","->","optimize.refresh"),t.page.scrolledY=t.scope[0][t.scope[1]];var o=t.table.getBoundingClientRect().top,r=Math.ceil(t.page.scrolledY-(o+t.page.scrolledY)+t.page.windowHeight/2);return new Promise((function(o,a){var s=0,l=Math.ceil(2*Math.ceil(t.page.windowHeight/t.rowHeight)),c=t.getActiveData(),u=c[0],d=c[1];s=t.scanForClosest(r,1e3,s),(s=t.getRelativeIndex(d,s)||s)>=0&&l?setTimeout((function(){t.setRows(s,u,l),(0,i.log)("optimize","Ran refresh in ".concat(performance.now()-n," ms.")),o(e)}),0):a()}))},this.attemptRefresh=function(){t.refreshId=t.refreshId||0,t.refreshId++,t.refreshing||(t.refreshing=!0,t.refresh(t.refreshId).then((function(){t.refreshing=!1})).catch((function(){t.refreshing=!1})))},this.init(e),this};t.default=r},4164:(e,t,n)=>{"use strict";t.__esModule=!0;var o=n(3987),i=function(){var e=this;return this.defaultDefinitions={FILTER_INPUT:':scope > div.filterContainer > input[type="text"]',TOP_EXTEND:":scope > div.topBar > div.extend",TABLE_CONTAINER:":scope > div.tableContainer",README_CONTAINER:":scope > .readmeContainer",TABLE:":scope > div.tableContainer > table",PATH:":scope > div.path"},this.init=function(){Object.keys(e.defaultDefinitions).forEach((function(t){e.define(e.defaultDefinitions[t],t)})),e.define("BODY","body",document)},this.define=function(t,n,i){void 0===n&&(n=null),void 0===i&&(i=null);var r=(i||document.body).querySelector(t),a=n||t;(0,o.isString)(a)&&(a=a.toUpperCase()),e.data[a]=r},this.use=function(t){if("string"==typeof t&&(t=t.toUpperCase()),!Object.prototype.hasOwnProperty.call(e.data,t)){var n=document.body.querySelector(t);if(!n)return!1;e.data[t]=n}return e.data[t]},this.data={},this.init(),this};t.default=i},5162:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0,t.componentBind=void 0;var i=n(8598),r=o(n(823)),a=n(6424),s=n(3987),l=n(9879),c=n(6931),u={},d=r.default.instances.selector,p={break:!1,save:null},f=null,v=["DOMMouseScroll","mousewheel","wheel"];u.handleTopBarVisibility=function(){var e=d.use("PATH"),t=document.body.querySelector(":scope > div.topBar > div.directoryInfo > div.quickPath");(0,s.getScrollTop)()<e.offsetTop+e.offsetHeight?t&&t._isVisible&&(t.classList.remove("visible"),t._isVisible=!1):(t||((t=s.DOM.new("div",{class:"quickPath","data-view":"desktop"})).innerHTML=e.innerHTML,document.body.querySelector(":scope > div.topBar > div.directoryInfo").append(t)),t._isVisible||(!1!==t._isVisible?requestAnimationFrame((function(){return t.classList.add("visible")})):t.classList.add("visible"),t._isVisible=!0))},u.handlePreviewScroll=function(e){if(r.default.scrollLock&&!p.break){if(e.deltaY&&0!==Math.abs(e.deltaY)){var t=r.default.preview.volume>=50?5:r.default.preview.volume>5?2:1;e.deltaY<0?(r.default.preview.volume=r.default.preview.volume+t,r.default.preview.volume>100&&(r.default.preview.volume=100)):e.deltaY>0&&(r.default.preview.volume=r.default.preview.volume+-Math.abs(t),r.default.preview.volume<0&&(r.default.preview.volume=0)),clearTimeout(p.save),p.save=window.setTimeout((function(){localStorage.setItem("".concat(c.StorageKey,".previewVolume"),r.default.preview.volume.toString())}),100),(0,l.log)("main","data.previewVolume",r.default.preview.volume,r.default.preview.data),r.default.preview.data&&r.default.preview.data.element&&r.default.preview.data.audible&&"VIDEO"===r.default.preview.data.type&&(0,s.setVideoVolume)(r.default.preview.data.element,r.default.preview.volume/100)}p.break=!0,setTimeout((function(){return p.break=!1}),25)}r.default.scrollLock&&e.preventDefault()};u.handleBaseScroll=function(){if(clearTimeout(f),f=setTimeout((function(){return u.handleTopBarVisibility(),void(r.default.instances.optimize.main.enabled&&r.default.instances.optimize.main.attemptRefresh())}),100),r.default.instances.optimize.main.enabled){var e=window.scrollY;Math.abs(e-r.default.layer.main.scrolledY)>175&&r.default.instances.optimize.main.attemptRefresh()}};var h=function(){return this.unbind=function(){a.eventHooks.unlisten(window,"keydown","mainKeyDown")},this.load=function(){a.eventHooks.listen(window,"keydown","mainKeyDown",(function(e){if(e.shiftKey&&e.code===c.Keys.f)e.preventDefault(),r.default.components.filter.toggle();else if(e.code===c.Keys.escape)r.default.components.main.overlay.hide((function(t){!0===t&&e.preventDefault()}));else if(e.code===c.Keys.g&&!0===i.config.get("gallery.enabled")){var t=document.body.querySelector(":scope > div.filterContainer");"none"!==t.style.display&&document.activeElement===t.querySelector("input")||(r.default.components.gallery.load(null),r.default.components.main.menu.toggle(!1))}})),v.forEach((function(e){a.eventHooks.listen(window,e,"handlePreviewScroll",u.handlePreviewScroll,{options:{passive:!1}})})),a.eventHooks.listen(window,"scroll","handleBaseScroll",u.handleBaseScroll,{options:{passive:!1}}),u.handleTopBarVisibility()},this};t.componentBind=h},4330:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0,t.componentFilter=void 0;var i=n(8598),r=o(n(823)),a=n(3987),s={},l=r.default.instances.selector;t.componentFilter=s,s.apply=function(e){void 0===e&&(e="");var t={},n=!1;r.default.sets.refresh=!0,t.reset=""===e||!e,t.shown={directories:0,files:0},t.size=0,r.default.instances.gallery&&(r.default.instances.gallery.data.selected.index=0);var o=Object.prototype.hasOwnProperty.call(i.config.get("sorting"),"directorySizes")&&i.config.get("sorting.directorySizes"),c=Object.prototype.hasOwnProperty.call(r.default.instances.optimize,"main")&&r.default.instances.optimize.main.enabled,u=c?r.default.instances.optimize.main.rows:null;null===u&&(u=l.use("TABLE").querySelectorAll("tbody > tr"));for(var d=1;d<u.length;d++){var p=u[d];if(!0!==t.reset){var f={file:!1,directory:!1};p.classList.contains("file")?f.file=!0:p.classList.contains("directory")&&(f.directory=!0);var v=s.getMatch(p.children[0].getAttribute("data-raw"),e);if(v.valid&&v.data?(p.classList.remove("filtered"),c&&r.default.instances.optimize.main.setVisibleFlag(p,!0),f.file?t.shown.files++:f.directory&&t.shown.directories++):v&&!1===v.valid?n=v.reason:(p.classList.add("filtered"),c&&r.default.instances.optimize.main.setVisibleFlag(p,!1)),v.valid&&v.data&&f.file||o&&v.valid&&v.data&&f.directory){var h=p.children[2].getAttribute("data-raw");t.size=isNaN(parseInt(h))?t.size:t.size+parseInt(h)}}else p.classList.remove("filtered"),c&&r.default.instances.optimize.main.setVisibleFlag(p,!0)}t.reset?l.use("TABLE_CONTAINER").removeAttribute("is-active-filter"):(l.use("TABLE_CONTAINER").setAttribute("is-active-filter",""),window.scrollTo(0,0)),c&&r.default.instances.optimize.main.refactor();var y={container:document.body.querySelector(":scope > div.topBar")};["size","files","directories"].forEach((function(e){y[e]=y.container.querySelector('[data-count="'.concat(e,'"]'))})),Object.prototype.hasOwnProperty.call(r.default.sets.defaults,"topValues")||(r.default.sets.defaults.topValues={size:y.size.textContent,files:y.files.textContent,directories:y.directories.textContent}),y.size.textContent=t.reset?r.default.sets.defaults.topValues.size:(0,a.getReadableSize)(i.config.get("format.sizes"),t.size),y.files.textContent=t.reset?r.default.sets.defaults.topValues.files:"".concat(t.shown.files," file").concat(1===t.shown.files?"":"s"),y.directories.textContent=t.reset?r.default.sets.defaults.topValues.directories:"".concat(t.shown.directories," ").concat(1===t.shown.directories?"directory":"directories");var g=document.body.querySelector(":scope > div.menu > #gallery"),m=l.use("TABLE_CONTAINER").querySelectorAll("table tr.file:not(.filtered) a.preview").length;!1!==n&&console.error("Filter regex error: ".concat(n)),!t.reset&&0===m&&g?"none"!==g.style.display&&(g.style.display="none"):(m>0||t.reset)&&g&&"none"===g.style.display&&(g.style.display="block")},s.getMatch=function(e,t){var n={};try{n.valid=!0,n.data=e.match(new RegExp(t,"i"))}catch(e){n.valid=!1,n.reason=e}return n},s.toggle=function(){var e=document.body.querySelector(":scope > div.filterContainer"),t=e.querySelector('input[type="text"]');"none"!==e.style.display?e.style.display="none":(t.value="",s.apply(null),e.style.display="block"),t.focus()}},3250:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0,t.componentGallery=void 0;var i=n(8598),r=o(n(823)),a=o(n(2333)),s=n(3987),l=n(6424),c=n(9879),u=function(){var e=this;return this.setOptions=function(e,t){return t.forEach((function(t){var n=t.shift();e[n]=i.config.get(t[0])})),e},this.load=function(t){if(void 0===t&&(t=0),!i.config.get("gallery.enabled"))return!1;(0,c.log)("gallery","loadIndex",t);var n={continue:{},preview:document.body.querySelector(":scope > div.preview-container > video")};if(n.source=n.preview?n.preview.querySelector("source"):null,n.source?(n.continue.src=n.source.getAttribute("src"),n.continue.time=n.preview.currentTime):n.continue=null,r.default.instances.gallery){r.default.instances.gallery.options.continue.video=n.continue;var o=r.default.sets.refresh?r.default.components.main.getTableItems():null;return r.default.sets.refresh=!1,r.default.sets.preview.video=null,(null===o||0!==o.length)&&void r.default.instances.gallery.show(!0,null===t?r.default.instances.gallery.data.selected.index:t,o)}var u=i.user.get(),d={},p=Object.prototype.hasOwnProperty.call(u.gallery,"listState")?u.gallery.listState:1;d.start=null===t?0:t,d=e.setOptions(d,[["console","debug"],["mobile","mobile"],["encodeAll","encodeAll"],["performance","performance"],["sharpen","gallery.imageSharpen"],["scrollInterval","gallery.scrollInterval"]]);var f={reverseOptions:["gallery","reverseOptions"],fitContent:["gallery","fitContent"],autoplay:["gallery","autoplay"],volume:["gallery","volume"]};Object.keys(f).forEach((function(e){(0,s.applyNested)(d,e,u,i.config.data.gallery[f[e][1]],f[e][0],f[e][1])})),d.list={show:null==p||!!p},(0,s.checkNested)(u,"gallery","listAlignment")?d.list.reverse=0!==u.gallery.listAlignment:d.list.reverse=!1,d.continue={video:n.continue};var v=r.default.components.main.getTableItems(),h=new a.default(v,Object.assign(d));r.default.instances.gallery=h,h&&l.eventHooks.subscribe("galleryVolumeChange","volumeWatcher",(function(e){(u=i.user.get()).gallery.volume=e,i.user.set(u)}))},this};t.componentGallery=u},7444:function(e,t,n){"use strict";var o=this&&this.__createBinding||(Object.create?function(e,t,n,o){void 0===o&&(o=n);var i=Object.getOwnPropertyDescriptor(t,n);i&&!("get"in i?!t.__esModule:i.writable||i.configurable)||(i={enumerable:!0,get:function(){return t[n]}}),Object.defineProperty(e,o,i)}:function(e,t,n,o){void 0===o&&(o=n),e[o]=t[n]}),i=this&&this.__exportStar||function(e,t){for(var n in e)"default"===n||Object.prototype.hasOwnProperty.call(t,n)||o(t,e,n)};t.__esModule=!0,i(n(5162),t),i(n(4330),t),i(n(3250),t),i(n(1069),t),i(n(104),t)},1069:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0,t.componentMain=void 0;var i=n(8834),r=n(8598),a=o(n(823)),s=n(6424),l=n(3987),c=a.default.instances.selector,u={menu:{},sort:{},overlay:{},dates:{}};u.menu.create=function(){var e=l.DOM.new("div",{class:"menu"}),t=[];return c.use("BODY").append(e),t.push({text:a.default.text.menuLabels.filter.text,id:"filter"}),t.push({text:a.default.text.menuLabels.wget.text,id:"copy"}),!0===r.config.get("gallery.enabled")&&c.use("TABLE").querySelectorAll(":scope > tbody > tr.file > td > a.preview").length>0&&t.unshift({text:a.default.text.menuLabels.gallery.text,id:"gallery"}),a.default.components.settings.available()&&t.unshift({text:a.default.text.menuLabels.settings.text,id:"settings",class:"settings"}),t.forEach((function(t){var n=l.DOM.new("div",{text:t.text,class:"".concat(Object.prototype.hasOwnProperty.call(t,"class")?"".concat(t.class):"")});Object.prototype.hasOwnProperty.call(t,"id")&&n.setAttribute("id",t.id),e.append(n)})),s.eventHooks.listen(e,"click","menuItemClick",(function(e){var t=e.target;if("DIV"===t.tagName){var n=function(e,t){e?u.menu.toggle(e):u.menu.toggle(),t()};"gallery"==t.id&&!0===r.config.get("gallery.enabled")?n(!1,(function(){return a.default.components.gallery.load(null)})):"copy"==t.id?n(!1,(function(){return(0,l.clipboardCopy)((0,l.generateWget)(c.use("TABLE")))})):"settings"==t.id?n(!1,(function(){return a.default.components.settings.show()})):"filter"==t.id&&n(null,(function(){return a.default.components.filter.toggle()}))}})),e},u.menu.toggle=function(e){void 0===e&&(e=null);var t=document.querySelector("body > div.menu"),n="none"===t.style.display,o="boolean"==typeof e?e?"inline-block":"none":n?"inline-block":"none";return l.DOM.style.set(t,{display:o}),n?c.use("TOP_EXTEND").setAttribute("extended","true"):c.use("TOP_EXTEND").removeAttribute("extended"),n},u.dates.offsetGet=function(){return(new Date).getTimezoneOffset()},u.dates.formatSince=function(e){if(0===e||e<0)return 0===e&&"Now";for(var t={year:31556926,month:2629743,week:604800,day:86e3,hour:3600,minute:60,second:1},n=Object.keys(t),o=n.length-1,i=!1,r=0;r<n.length;r++){var a=n[r];if(!(e<=t[a])){var s=o>=r+1?n[r+1]:null,l=Math.floor(e/t[a]),c=s?Math.floor((e-l*t[a])/t[s]):0;i="".concat(l," ").concat(a).concat(1==l?"":"s")+(c>0?" and ".concat(c," ").concat(s).concat(1==c?"":"s"):"")+" ago";break}}return i},u.dates.apply=function(e,t){void 0===t&&(t=!0);var n=r.config.get("timestamp"),o=r.config.get("format.date");c.use("TABLE").querySelectorAll("tr.directory > td:nth-child(2), tr.file > td[data-raw]:nth-child(2)").forEach((function(a){var s=parseInt(a.getAttribute("data-raw")),c=u.dates.formatSince(n-s),d=!0===t?l.DOM.new("span"):a.querySelector(":scope > span");!0===t&&(o.forEach((function(e,t){if(t<=1){var n=l.DOM.new("span",{text:(0,i.formatDate)(e,s)});r.config.get("format.date").length>1&&n.setAttribute("data-view",0===t?"desktop":"mobile"),d.appendChild(n)}})),a.innerHTML=d.innerHTML),c&&d.setAttribute("title","".concat(c," (UTC").concat((e.hours>0?"+":"")+e.hours,")"))}));document.body.querySelectorAll('div.topBar > .directoryInfo div[data-count="files"], div.topBar > .directoryInfo div[data-count="directories"]').forEach((function(e){e.hasAttribute("data-raw")&&e.setAttribute("title","Newest: ".concat((0,i.formatDate)(o[0],parseInt(e.getAttribute("data-raw")))))}))},u.dates.load=function(){var e=u.dates.offsetGet(),t=r.user.get(),n=t.timezoneOffset!==e;n&&(t.timezoneOffset=e,r.user.set(t));var o={minutes:e>0?-Math.abs(e):Math.abs(e)};o.hours=o.minutes/60,o.seconds=60*o.minutes,u.dates.apply(o,n)},u.sort.load=function(){if(r.config.exists("sorting")&&r.config.get("sorting.enabled")){var e=r.config.get("sorting.types");if(0===e||1===e){var t="asc"===r.config.get("sorting.order"),n=null;switch(r.config.get("sorting.sortBy")){case"name":n=0;break;case"modified":n=1;break;case"size":n=2;break;case"type":n=3;break;default:n=null}if(null!==n){var o=document.querySelectorAll("table th span[sortable]")[n].closest("th");if(o){o.asc=t;var i=o.querySelector(":scope > span.sortingIndicator");i&&i.classList.add(t?"down":"up","visible")}}}}},u.overlay.hide=function(e){void 0===e&&(e=null);var t=0,n=[];n.push({element:document.body.querySelector(":scope > div.filterContainer"),f:a.default.components.filter.toggle}),n.push({element:document.body.querySelector(":scope > div.menu"),f:u.menu.toggle}),n.forEach((function(e){e.element&&"none"!==e.element.style.display&&(e.f(),t++)})),e&&e(t>0)},u.getTableItems=function(){var e=[];if(a.default.instances.optimize.main.enabled){for(var t=a.default.instances.optimize.main.getActiveData()[0],n=0;n<t.length;n++)if(0!==n){var o=t[n],i=o.children[0].children[0];i.classList.contains("preview")&&e.push({url:i.getAttribute("href"),name:o.children[0].getAttribute("data-raw"),size:o.children[2].textContent})}}else c.use("TABLE").querySelectorAll(":scope > tbody > tr.file:not(.filtered) > td:first-child > a.preview").forEach((function(t){var n=t.parentNode,o=n.parentNode,i=t.getAttribute("href");void 0!==i&&e.push({url:i,name:n.getAttribute("data-raw"),size:o.querySelector("td:nth-child(3)").innerHTML})}));return e},u.sortTableColumn=function(e){var t=e.closest("th"),n="TH"!==e.tagName?t:e,o=l.DOM.getIndex(n),i={directories:Array.from(c.use("TABLE").querySelectorAll("tbody > tr.directory")),files:Array.from(c.use("TABLE").querySelectorAll("tbody > tr.file"))},s=!(Object.prototype.hasOwnProperty.call(r.config.get("sorting"),"directorySizes")&&r.config.get("sorting.directorySizes"))&&r.config.exists("sorting.sortBy")&&(2===o||3===o);a.default.instances.optimize.main.enabled?a.default.instances.optimize.main.sortRows(o,n.asc?"desc":"asc"):(0!==(d=r.config.get("sorting.types"))&&2!==d||s||i.directories.sort((0,l.comparer)(o)),0!==d&&1!==d||i.files.sort((0,l.comparer)(o)));n.asc=!n.asc,document.body.querySelectorAll(":scope > div.tableContainer > table > \t\tthead > tr > th span.sortingIndicator").forEach((function(e){e.classList.remove("up","down","visible")})),t.querySelector(":scope > span.sortingIndicator").classList.add(n.asc?"down":"up","visible");var u=r.user.get();if(u.sort.ascending=n.asc?1:0,u.sort.row=o,r.user.set(u),!a.default.instances.optimize.main.enabled){var d;if(!n.asc)0!==(d=r.config.get("sorting.types"))&&2!==d||s||(i.directories=i.directories.reverse()),0!==d&&1!==d||(i.files=i.files.reverse());var p=c.use("TABLE").querySelector("tbody");Object.keys(i).forEach((function(e){i[e].forEach((function(e){p.append(e)}))}))}a.default.sets.refresh=!0,a.default.sets.selected=null};var d=u;t.componentMain=d},104:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0,t.componentSettings=void 0;var i=o(n(823)),r=n(8598),a=n(3987),s=n(6424),l=n(9879),c={},u={style:{},gallery:{}},d={},p={};c.option=function(e,t,n,o){void 0===n&&(n={}),void 0===o&&(o=null),Object.prototype.hasOwnProperty.call(n,"class")&&(n.class="option "+n.class);var i=Object.assign({class:"option"},n),r={class:"option-text",text:t};o&&(r.title=o);var s=a.DOM.wrap(a.DOM.wrap(e,"div"),"div",i);return s.prepend(a.DOM.new("div",r)),s},c.section=function(e,t){void 0===t&&(t=null);var n=a.DOM.new("div",{class:"section","data-key":e});return n.appendChild(a.DOM.new("div",{class:"header",text:t||(0,a.capitalize)(e)})),n},c.select=function(e,t,n){void 0===t&&(t={}),void 0===n&&(n=null);var o=a.DOM.new("select",t);return e.map((function(e,t){e.text=(0,a.capitalize)(e.text);var i=a.DOM.new("option",e);return null!==n&&!0===n(i,t,o)&&(i.selected=!0,o.selectedIndex=t),i})).forEach((function(e){o.appendChild(e)})),o},c.check=function(e,t){void 0===t&&(t=null);var n=null!==t&&t();n&&(e.checked="");var o=a.DOM.new("input",Object.assign(e,{type:"checkbox"}));return o.checked=n,o},u.style.theme=function(e){d.set(!1===e?null:e,!1)},u.style.compact=function(e){document.body.classList[e?"add":"remove"]("compact")},u.gallery.listAlignment=function(e){if(i.default.instances.gallery){var t=document.body.querySelector(":scope > div.rootGallery > div.galleryContent");t.classList[0===e?"remove":"add"]("reversed");var n=t.querySelector(":scope > div.list"),o=t.querySelector(":scope > div.media");t.querySelector(":scope > div.list").remove(),o.parentNode.insertBefore(n,1===e?o:o.nextSibling),i.default.instances.gallery.options.list.reverse=0!==e}},u.gallery.reverseOptions=function(e){if(i.default.instances.gallery){i.default.instances.gallery.options.reverseOptions=e;var t=document.body.querySelector("div.rootGallery > div.galleryContent > \t\t\tdiv.media > div.wrapper > div.cover .reverse");t&&t.remove()}},u.gallery.fitContent=function(e){if(i.default.instances.gallery){i.default.instances.gallery.options.fitContent=e;var t=document.body.querySelector("div.rootGallery > div.galleryContent > div.media > div.wrapper");t&&e?(t.classList.add("fill"),i.default.sets.refresh=!0,i.default.sets.selected=null):t&&(t.classList.remove("fill"),[".cover",".cover img","video"].forEach((function(e){a.DOM.style.set(t.querySelector(e),{height:"",width:""})})))}},u.gallery.autoplay=function(e){i.default.instances.gallery&&(i.default.instances.gallery.options.autoplay=e)},p.gather=function(e){var t={};return e.querySelectorAll('select, input[type="checkbox"]').forEach((function(e){if(e.hasAttribute("name")){var n=e.getAttribute("name"),o=e.hasAttribute("data-key")?e.getAttribute("data-key"):e.closest(".section").getAttribute("data-key");if(Object.prototype.hasOwnProperty.call(t,o)||(t[o]={}),"SELECT"===e.tagName){var i="theme"===n?e[e.selectedIndex].value:e.selectedIndex;t[o][n]=i}else"INPUT"===e.tagName&&"CHECKBOX"===e.getAttribute("type").toUpperCase()&&(t[o][n]=e.checked)}})),t},p.set=function(e,t){t=t||r.user.get();var n=!1;return Object.keys(e).forEach((function(o){var i="main"===o;i||Object.prototype.hasOwnProperty.call(t,o)||(t[o]={}),Object.keys(e[o]).forEach((function(s){var l=null;if("theme"===s){if(Object.prototype.hasOwnProperty.call(r.config.get("style.themes.pool"),e[o][s])){var c=e[o][s];l="default"!==c&&c}}else l=e[o][s];var d=i?t[s]!==l:t[o][s]!==l;e[o][s]={value:l,changed:d},i?t[s]=l:t[o][s]=l,d&&(i&&Object.prototype.hasOwnProperty.call(u,s)?u[s](l):(0,a.checkNested)(u,o,s)&&u[o][s](l),"theme"===s&&(n=!0))}))})),(0,l.log)("settings","Set settings:",e),r.user.set(t),n&&location.reload(),e},d.set=function(e,t){void 0===e&&(e=null),void 0===t&&(t=!0);var n=r.config.get("style.themes.path"),o=r.config.get("style.themes.pool.".concat(e,".path")),i=document.querySelectorAll('head > link[rel="stylesheet"]'),s=Array.from(i)||[];if(s.length>0&&(s=(s=Array.from(s)).filter((function(e){return e.hasAttribute("href")&&e.getAttribute("href").includes(n)}))),r.config.set("style.themes.set",e),!e)return s.length>0&&s.forEach((function(e){return e.remove()})),!1;if(t&&r.user.set(r.user.get().style.theme=e),o){var l=a.DOM.new("link",{rel:"stylesheet",type:"text/css",href:"".concat(o,"?bust=").concat(r.config.data.bust).replace(/\/\//g,"/")});document.querySelector("head").append(l),s.length>0&&s.forEach((function(e){return e.remove()}))}};var f=function(){var e=this;return this.available=function(){return!!(r.config.exists("style.themes.pool")&&Object.keys(r.config.get("style.themes.pool")).length>0||!0===r.config.get("gallery.enabled"))},this.apply=function(e,t){t=t||r.user.get(),p.set(p.gather(e),t),i.default.components.settings.close(),i.default.layer.main.update()},this.close=function(){Object.keys(e.boundEvents).forEach((function(t){var n=e.boundEvents[t],o=n.selector,i=n.events;s.eventHooks.unlisten(o,i,t)})),e.boundEvents={},document.body.querySelectorAll(":scope > div.focusOverlay, :scope > div.settingsContainer").forEach((function(e){return e.remove()}))},this.getSectionGallery=function(t,n){void 0===t&&(t=c.section("gallery")),void 0===n&&(n=0),r.config.get("mobile")||(t.append(c.option(c.select(["right","left"].map((function(e){return{value:"align-"+e,text:e}})),{name:"listAlignment"},(function(t,n){return n===e.client.gallery.listAlignment})),i.default.text.settingsLabels.galleryListAlignment.text)),n++);var o=[];return o.push([i.default.text.settingsLabels.galleryReverseSearch.text,"reverseOptions",i.default.text.settingsLabels.galleryReverseSearch.description]),o.push([i.default.text.settingsLabels.galleryVideoAutoplay.text,"autoplay",i.default.text.settingsLabels.galleryVideoAutoplay.description]),o.push([i.default.text.settingsLabels.galleryFitContent.text,"fitContent",i.default.text.settingsLabels.galleryFitContent.description]),o.forEach((function(o){var i=o[0],s=o[1],l=o[2];t.append(c.option(c.check({name:s},(function(){return(0,a.checkNested)(e.client,"gallery",s)?e.client.gallery[s]:r.config.get("gallery.".concat(s))})),i,{class:"interactable"},l)),n++})),{settings:n,section:t}},this.getSectionMain=function(t,n){if(void 0===t&&(t=c.section("main")),void 0===n&&(n=0),r.config.exists("style.themes.pool")&&"object"==typeof r.config.get("style.themes.pool")){var o=r.config.get("style.themes.pool"),s=Object.keys(o),l=r.config.get("style.themes.set"),u=[s.map((function(e){return{value:e,text:e}})),{name:"theme","data-key":"style"},function(e,t){return null===l&&0===t||e.value===l}];t.append(c.option(c.select.apply(c,u),"Theme")),n++}if(r.config.exists("style.compact")&&!r.config.get("mobile")){var d=[{name:"compact","data-key":"style"},function(){return(0,a.checkNested)(e.client,"style","compact")?e.client.style.compact:r.config.get("style.compact")}],p=c.check.apply(c,d),f=c.option(p,i.default.text.settingsLabels.stylingCompact.text,{class:"interactable"},i.default.text.settingsLabels.stylingCompact.description);t.appendChild(f),n++}return{settings:n,section:t}},this.removeOnUnbind=function(t){var n=t.selector,o=t.events,i=t.id;e.boundEvents[i]={selector:n,events:o}},this.show=function(){if(!document.body.querySelector(":scope > div.settingsContainer")){e.client=r.user.get(),e.boundEvents={};var t=[];if(!document.body.querySelector(":scope > div.focusOverlay")){var n=a.DOM.new("div",{class:"focusOverlay"});document.body.appendChild(n),s.eventHooks.listen(n,"click","settingsOverlayClick",(function(){i.default.components.settings.close()}),{onAdd:e.removeOnUnbind})}var o=a.DOM.new("div",{class:"settingsContainer"});t.push(e.getSectionMain()),r.config.get("gallery.enabled")&&t.push(e.getSectionGallery());var l=a.DOM.new("div",{class:"wrapper"});l.append.apply(l,t.map((function(e){return e.settings>0?e.section:null})).filter((function(e){return null!==e})));var c=a.DOM.new("div",{class:"bottom"}),u=a.DOM.new("div",{class:"apply ns",text:"Apply"}),d=a.DOM.new("div",{class:"cancel ns",text:"Cancel"});o.append(l,c),[[u,"settignsApplyClick",function(){return e.apply(o,e.client)}],[d,"settingsCancelClick",function(){return e.close()}]].forEach((function(t){var n=t[0],o=t[1],i=t[2];c.append(n),s.eventHooks.listen(n,"click",o.toString(),i,{onAdd:e.removeOnUnbind})})),document.body.appendChild(o),o.querySelectorAll("div.section > .option.interactable").forEach((function(t,n){s.eventHooks.listen(t,"mouseup","settingsMouseUp_".concat(n),(function(e){if(!window.getSelection().toString()&&"INPUT"!==e.target.tagName){var t=e.currentTarget.querySelector('input[type="checkbox"]');if(t)return void(t.checked=!t.checked)}}),{onAdd:e.removeOnUnbind})}))}},this};t.componentSettings=f},8598:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0,t.user=t.config=void 0;var i=o(n(6808));n(2515);var r=n(3987),a=n(6931),s={};t.config=s;var l={};t.user=l,s.init=function(){s.data=JSON.parse(document.getElementById(a.ScriptDataId).innerHTML),s.data.mobile=Modernizr.mq("(max-width: 640px)")},s.isMobile=function(){return s.data.mobile},s.exists=function(e){return(0,r.checkNestedPath)(s.data,e)},s.set=function(e,t){return(0,r.setNestedPath)(s.data,e,t)},s.get=function(e){return(0,r.getNestedPath)(s.data,e,null)},l.set=function(e,t){void 0===t&&(t={}),t=Object.assign({sameSite:"lax",expires:365},t),i.default.set(a.CookieKey,JSON.stringify(e),t)},l.getDefaults=function(){var e={};return e.gallery={reverseOptions:s.data.gallery.reverseOptions,listAlignment:s.data.gallery.listAlignment,fitContent:s.data.gallery.fitContent,autoplay:!0,volume:.25},e.style={compact:s.data.style.compact,theme:!1},e},l.get=function(){var e=["gallery","sort","style"],t=l.getDefaults(),n={},o=!1;try{n=JSON.parse(i.default.get(a.CookieKey)),e.forEach((function(e){Object.prototype.hasOwnProperty.call(n,e)||(n[e]=Object.prototype.hasOwnProperty.call(t,e)?t[e]:{})})),Object.keys(t).forEach((function(e){Object.keys(t[e]).forEach((function(i){Object.prototype.hasOwnProperty.call(n[e],i)||(n[e][i]=t[e][i],o=!0)}))})),o&&l.set(n)}catch(o){n={},s.data.style.themes.set&&(t.style||(t.style={}),t.style.theme=s.data.style.themes.set),e.forEach((function(e){n[e]={}})),l.set(Object.assign(n,t))}return n},s.init(),l.get()},823:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0;var i=n(8598),r=o(n(564)),a=o(n(4164)),s=n(6931),l={};if(l.text=r.default,l.scrollLock=!1,l.sets={preview:{},defaults:{},selection:{},selected:null,refresh:!1},l.components={},l.layer={},l.instances={},l.instances.optimize={main:{enabled:!1},gallery:{enabled:!1}},l.preview={volume:0,isLoadable:!0,data:null},!1===i.config.get("mobile")&&!0===i.config.get("preview.enabled")){var c=localStorage.getItem("".concat(s.StorageKey,".previewVolume"));l.preview.volume=parseInt(c)?parseInt(c):null,null!==l.preview.volume&&l.preview.volume>=0?l.preview.volume=parseInt(l.preview.volume.toString()):(l.preview.volume=10,localStorage.setItem("".concat(s.StorageKey,".previewVolume"),"10"))}l.instances.selector=new a.default,t.default=l},6931:(e,t)=>{"use strict";t.__esModule=!0,t.ScriptDataId=t.CookieKey=t.StorageKey=t.Keys=void 0;t.Keys={escape:"Escape",pageUp:"PageUp",pageDown:"PageDown",arrowLeft:"ArrowLeft",arrowUp:"ArrowUp",arrowRight:"ArrowRight",arrowDown:"ArrowDown",f:"KeyF",g:"KeyG",l:"KeyL"};t.StorageKey="IVFi";t.CookieKey="IVFi";t.ScriptDataId="__IVFI_DATA__"},4578:(e,t)=>{"use strict";t.__esModule=!0,t.clipboardCopy=void 0;t.clipboardCopy=function(e){if(navigator.clipboard)navigator.clipboard.writeText(e).then((function(){console.log("clipboardCopy",{type:"async",successful:!0})})).catch((function(){console.log("clipboardCopy",{type:"async",successful:!1})}));else{var t=document.createElement("textarea");t.value=e,t.style.top="0",t.style.left="0",t.style.position="fixed",document.body.appendChild(t),t.focus(),t.select();try{var n=document.execCommand("copy");console.log("clipboardCopy",{type:"Fallback",successful:n})}catch(e){console.log("clipboardCopy",{type:"Fallback",successful:!1})}document.body.removeChild(t)}}},7735:(e,t)=>{"use strict";t.__esModule=!0,t.comparer=void 0;var n=function(e,t){var n=e.querySelector("td:nth-child(".concat(t+1,")")).getAttribute("data-raw");return void 0!==n?n:e.querySelector("td:nth-child(".concat(t+1,")")).textContent};t.comparer=function(e){return function(t,o){var i=n(t,e),r=n(o,e);return!isNaN(parseFloat(i))&&isFinite(i)&&!isNaN(parseFloat(r))&&isFinite(r)?i-r:i.localeCompare(r)}}},4975:(e,t)=>{"use strict";t.__esModule=!0,t.debounce=void 0;t.debounce=function(e){var t=null;return function(n){t&&clearTimeout(t),t=window.setTimeout(e,100,n)}}},4280:(e,t)=>{"use strict";t.__esModule=!0,t.DOM=void 0,t.DOM={cache:{id:0},new:function(e,n){void 0===n&&(n={});var o=document.createElement(e),i=Object.keys(n);return o.domId=t.DOM.cache.id,i.forEach((function(e){"text"===e.toLowerCase()?o.textContent=n[e]:o.setAttribute(e,n[e])})),t.DOM.cache.id++,o},wrap:function(e,n,o){var i=t.DOM.new(n,o);return i.appendChild(e),i},style:{set:function(e,t){e&&Object.keys(t).forEach((function(n){e.style[n]=t[n]}))}},attributes:{set:function(e,t){e&&Object.keys(t).forEach((function(n){e.setAttribute(n,t[n])}))}},getIndex:function(e){return Array.from(e.parentNode.children).indexOf(e)}}},8440:(e,t)=>{"use strict";t.__esModule=!0,t.generateWget=void 0;t.generateWget=function(e){var t=window.location.href,n=[];return e.querySelectorAll("tr.file:not(.filtered) > td:first-child > a").forEach((function(e){var t=e.textContent.split(".").pop().toLowerCase().trim();n.includes(t)||n.push(t)})),'wget -r -np -nH -nd -e robots=off --accept "'.concat(n.join(","),'" "').concat(t,'"')}},1587:(e,t)=>{"use strict";t.__esModule=!0,t.getReadableSize=void 0;t.getReadableSize=function(e,t){if(void 0===t&&(t=0),0===t)return"0.00".concat(e[0]);var n=0;do{t/=1024,n++}while(t>1024);return Math.max(t,.1).toFixed(n<2?0:2)+e[n]}},6261:(e,t)=>{"use strict";t.__esModule=!0,t.getScrollTop=void 0;t.getScrollTop=function(){return window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop||0}},3987:function(e,t,n){"use strict";var o=this&&this.__createBinding||(Object.create?function(e,t,n,o){void 0===o&&(o=n);var i=Object.getOwnPropertyDescriptor(t,n);i&&!("get"in i?!t.__esModule:i.writable||i.configurable)||(i={enumerable:!0,get:function(){return t[n]}}),Object.defineProperty(e,o,i)}:function(e,t,n,o){void 0===o&&(o=n),e[o]=t[n]}),i=this&&this.__exportStar||function(e,t){for(var n in e)"default"===n||Object.prototype.hasOwnProperty.call(t,n)||o(t,e,n)};t.__esModule=!0,i(n(7548),t),i(n(8146),t),i(n(4975),t),i(n(4578),t),i(n(7735),t),i(n(4280),t),i(n(6261),t),i(n(1587),t),i(n(8440),t),i(n(1424),t),i(n(7016),t),i(n(2295),t)},8146:(e,t)=>{"use strict";t.__esModule=!0,t.isNumeric=void 0;t.isNumeric=function(e){return!isNaN(parseFloat(e))&&isFinite(e)}},7548:(e,t)=>{"use strict";t.__esModule=!0,t.isString=void 0;t.isString=function(e){return"string"==typeof e}},7016:function(e,t){"use strict";var n=this&&this.__spreadArray||function(e,t,n){if(n||2===arguments.length)for(var o,i=0,r=t.length;i<r;i++)!o&&i in t||(o||(o=Array.prototype.slice.call(t,0,i)),o[i]=t[i]);return e.concat(o||Array.prototype.slice.call(t))};t.__esModule=!0,t.objHas=t.applyNested=t.getNested=t.getNestedPath=t.setNestedPath=t.checkNested=t.checkNestedPath=void 0;t.checkNestedPath=function(e,t){t=Array.isArray(t)?t:t.split(".");for(var n=0;n<t.length;n++){if(!e||!Object.prototype.hasOwnProperty.call(e,t[n]))return!1;e=e[t[n]]}return!0};t.checkNested=function(e){for(var t=[],n=1;n<arguments.length;n++)t[n-1]=arguments[n];for(var o=0;o<t.length;o++){if(!e||!Object.prototype.hasOwnProperty.call(e,t[o]))return!1;e=e[t[o]]}return!0};t.setNestedPath=function(e,t,n){t=Array.isArray(t)?t:t.split(".");for(var o=!1,i=0;i<t.length;i++)e&&Object.prototype.hasOwnProperty.call(e,t[i])&&(i===t.length-1&&(e[t[i]]=n,o=!0),e=e[t[i]]);return o};t.getNestedPath=function(e,t,n){t=Array.isArray(t)?t:t.split(".");for(var o=0;o<t.length;o++){if(!e||!Object.prototype.hasOwnProperty.call(e,t[o]))return n;e=e[t[o]]}return e};t.getNested=function(e,t){for(var n=[],o=2;o<arguments.length;o++)n[o-2]=arguments[o];for(var i=0;i<n.length;i++){if(!e||!Object.prototype.hasOwnProperty.call(e,n[i]))return t;e=e[n[i]]}return e};t.applyNested=function(e,o,i,r){for(var a=[],s=4;s<arguments.length;s++)a[s-4]=arguments[s];if(e){var l=t.checkNested.apply(void 0,n([i],a,!1));return e[o]=l?t.getNested.apply(void 0,n([i,r],a,!1)):r,e[o]}return null};t.objHas=function(e,n){var o=null,i=[];return o=n.includes(".")?(i=n.split(".")).shift():n,void 0!==e&&(!(0!==i.length||!Object.prototype.hasOwnProperty.call(e,o))||(0,t.objHas)(e[o],i.join(".")))}},2295:(e,t,n)=>{"use strict";t.__esModule=!0,t.setVideoVolume=t.showVolumeIndicator=void 0;var o=n(4280),i=null;t.showVolumeIndicator=function(e){clearTimeout(i);var t=document.body.querySelector(":scope > div#indicatorPreviewVolume"),n=0===e?"Muted":"Volume: ".concat(e,"%");t?t.textContent=n:(t=o.DOM.new("div",{id:"indicatorPreviewVolume",text:n}),document.body.prepend(t)),setTimeout((function(){o.DOM.style.set(t,{opacity:"1"})})),i=window.setTimeout((function(){o.DOM.style.set(t,{opacity:"0"})}),2500)};t.setVideoVolume=function(e,n,o){if(void 0===o&&(o=!0),e){var i=!(n>0);e.muted=i,e.volume=i?0:n<=100?n:100,e.play().then((function(){o&&(0,t.showVolumeIndicator)(Math.round(100*e.volume))})).catch((function(){e.muted=!0,e.volume=0,o&&(0,t.showVolumeIndicator)(Math.round(100*e.volume)),e.play()}))}}},1424:(e,t)=>{"use strict";t.__esModule=!0,t.stripUrl=t.identifyExtension=t.shortenString=t.capitalize=void 0;t.capitalize=function(e){return e.charAt(0).toUpperCase()+e.slice(1)};t.shortenString=function(e,t){return t=t||28,e.length>t?[e.substring(0,Math.floor(t/2-2)),e.substring(e.length-Math.floor(t/2-2),e.length)].join(" .. "):e};t.identifyExtension=function(e,t){void 0===t&&(t={image:[],video:[]});var n=e.split(".").pop().toLowerCase();return t.image.includes(n)?[n,0]:t.video.includes(n)?[n,1]:null};t.stripUrl=function(e){return e.includes("?")?e.split("?")[0]:e}},3937:function(e,t,n){"use strict";var o=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};t.__esModule=!0;var i=o(n(4933)),r=n(8598),a=o(n(823)),s=n(9879),l=n(6424),c=o(n(152)),u=n(7444),d=n(3987);n(7975),n(1007),n(3858);var p=a.default.instances.selector;try{navigator.mediaSession.setActionHandler("play",null)}catch(e){(0,s.log)("error",e)}if(a.default.components.main=u.componentMain,a.default.layer.main={windowHeight:window.innerHeight,windowWidth:window.innerWidth,scrolledY:window.scrollY,update:function(){var e=p.use("TABLE_CONTAINER");return a.default.layer.main.windowHeight=window.innerHeight,a.default.layer.main.windowWidth=window.innerWidth,a.default.layer.main.scrolledY=window.scrollY,a.default.layer.main.tableWidth=e.offsetWidth,!0}},a.default.layer.main.update(),r.config.get("performance")){var f=function(e){for(var t=0,n=0;n<e.length;n++)e[n].children[0].children[0].classList.contains("preview")&&(e[n]._mediaIndex=t,t++)};setTimeout((function(){requestAnimationFrame((function(){a.default.instances.optimize.main=new c.default({page:a.default.layer.main,table:p.use("TABLE"),scope:[window,"scrollY"],padding:0,on:{rowChange:f}})}))}),1)}if(l.eventHooks.listen(p.use("TOP_EXTEND"),"click","sortClick",(function(e){a.default.components.main.menu.toggle(e.currentTarget)})),l.eventHooks.listen(p.use("FILTER_INPUT"),"input","filterInput",(function(e){a.default.components.filter.apply(e.currentTarget.value)})),p.use("README_CONTAINER")&&l.eventHooks.listen(p.use("README_CONTAINER"),"toggle","toggledReadme",(function(e){var t=r.user.get();t.readme||(t.readme={}),t.readme.toggled=e.target.hasAttribute("open"),r.user.set(t),a.default.instances.optimize.main.enabled&&a.default.instances.optimize.main.attemptRefresh()})),l.eventHooks.listen(p.use("TABLE"),"click","sortClick",(function(e){var t=e.target;if("SPAN"===t.tagName&&t.hasAttribute("sortable"))a.default.components.main.sortTableColumn(t);else if(!0===r.config.get("gallery.enabled")&&"A"===t.tagName&&"preview"==t.className){e.preventDefault();var n=0;if(a.default.instances.optimize.main.enabled){var o=t.closest("tr");o._mediaIndex&&(n=o._mediaIndex)}else p.use("TABLE").querySelectorAll("tr.file:not(.filtered) a.preview").forEach((function(e,o){t===e&&(n=o)}));a.default.components.gallery.load(n)}})),l.eventHooks.listen(window,"resize","windowResize",(0,d.debounce)((function(){(0,s.log)("event","windowResize (main)","Resized."),r.config.set("mobile",Modernizr.mq("(max-width: 640px)")),a.default.instances.gallery&&(a.default.instances.gallery.options.mobile=r.config.get("mobile"),a.default.instances.gallery.update.listWidth()),a.default.layer.main.update(),a.default.instances.optimize.main.enabled&&a.default.instances.optimize.main.attemptRefresh()}))),!1===r.config.get("mobile")&&!0===r.config.get("preview.enabled")){var v={},h=null,y=null,g=function(e){if((0,s.log)("preview","Preview loaded =>",e),a.default.preview.data&&a.default.preview.data.element&&a.default.preview.data.element.remove(),!a.default.preview.isLoadable)return null;var t=[e.element,e.type,e.src],n=t[0],o=t[1],i=t[2];if(a.default.preview.data=e,clearInterval(y),n&&"VIDEO"===o?(h&&h.src===i?n.currentTime=h.timestamp:h=null,(0,d.setVideoVolume)(n,a.default.preview.volume/100,!1),y=setInterval((function(){n.readyState>1&&(d.DOM.style.set(n,{visibility:"visible"}),clearInterval(y))}),25)):h=null,Object.prototype.hasOwnProperty.call(e,"timestamp")){var r=e.timestamp;h={src:i,timestamp:r}}e.loaded&&e.audible?a.default.scrollLock=!0:a.default.scrollLock=!1},m=function(e){var t=e.getAttribute("href"),n=r.config.get("extensions"),o=(0,d.identifyExtension)((0,d.stripUrl)(t),{image:n.image,video:n.video});if(o){var a=o[0],s=o[1],l={};l.delay=r.config.get("preview.hoverDelay"),l.cursor=r.config.get("preview.cursorIndicator"),l.encodeAll=r.config.get("encodeAll"),l.on={onLoaded:g},l.force={extension:a,type:s},v[e.itemIndex]=(0,i.default)(e,l)}};document.querySelectorAll("body > div.tableContainer > table > tbody > tr.file > td > a.preview").forEach((function(e,t){e.itemIndex=t,0===t&&m(e)})),l.eventHooks.listen(p.use("TABLE"),"mouseover","previewMouseEnter",(function(e){if("A"===e.target.tagName&&"preview"==e.target.className){var t=e.target.itemIndex;Object.prototype.hasOwnProperty.call(v,t)||m(e.target)}}))}if(r.config.get("singlePage")){var b=!1,w=function(e,t){if(void 0===t&&(t=!0),!b){b=!0;var n=window.location.protocol,o=window.location.port,i=window.location.hostname+(o&&"80"!==o||"443"!==o?":"+o:""),l=e.replace(/([^:]\/)\/+/g,"$1").replace(/^\/|\/$/g,""),c="".concat(n,"//").concat(i,"/").concat(l?l+"/":""),u=r.config.get("format").title.replace("%s","/".concat(l,"/")),d=Object.entries({navigateType:"dynamic"}).map((function(e,t){var n=e[0],o=e[1];return"".concat(t>0?"&":"").concat(n,"=").concat(o)})).join("");a.default.preview.isLoadable=!1;var p=document.createElement("div");p.classList.add("navigateLoad"),document.body.prepend(p),setTimeout((function(){return p.style.opacity="1"}),10);var f=function(){b=!1,p.remove(),a.default.preview.isLoadable=!0};fetch("/".concat(l,"/"),{method:"POST",redirect:"follow",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:d}).then((function(e){"dynamic"===e.headers.get("navigate-type")?((0,s.log)("main","Valid header. Navigating pages .."),e.text().then((function(e){document.eventHooks=null,window.eventHooks=null,t&&window.history.pushState({path:"/"+l},u,c),document.open(),document.write(e),document.close(),window.scrollTo({top:0,behavior:"auto"})}))):(f(),window.location.replace(c))})).catch((function(){f(),window.location.replace(c)}))}};l.eventHooks.listen(window,"popstate","mainPopState",(function(){w(window.location.pathname,!1)})),l.eventHooks.listen(p.use("TABLE"),"click","tableClick",(function(e){if("A"===e.target.tagName){var t=e.target.closest("tr");t&&(t.classList.contains("directory")||t.classList.contains("parent"))&&(e.preventDefault(),w(e.target.getAttribute("href")))}}));var O=document.body.querySelector(":scope > div.topBar > div.directoryInfo"),S=document.body.querySelector(":scope > div.path");l.eventHooks.listen(O,"click","quickPathClick",(function(e){if("A"===e.target.tagName){var t=e.target.parentNode;t&&t.classList.contains("quickPath")&&(e.preventDefault(),w(e.target.getAttribute("href")))}})),l.eventHooks.listen(S,"click","pathClick",(function(e){"A"===e.target.tagName&&(e.preventDefault(),w(e.target.getAttribute("href")))}))}a.default.components.settings=new u.componentSettings,a.default.components.gallery=new u.componentGallery,a.default.components.bind=new u.componentBind,a.default.components.filter=u.componentFilter,a.default.components.main.bind=a.default.components.bind.load,a.default.components.main.unbind=a.default.components.bind.unbind,a.default.components.main.bind(),a.default.components.main.dates.load(),document.body.querySelector(':scope > .filterContainer > input[type="text"]').value="";var k=a.default.components.main.menu.create(),M=document.querySelector("body > div.topBar").offsetHeight;k&&M&&d.DOM.style.set(k,{top:"".concat(M,"px"),visibility:"unset",display:"none"}),u.componentMain.sort.load(),document.body.removeAttribute("is-loading"),l.eventHooks.subscribe("galleryBound","mainUnbind",(function(){a.default.components.main.unbind()})),l.eventHooks.subscribe("galleryUnbound","mainBind",(function(){a.default.components.main.bind()})),(0,s.log)("main","Config loaded =>",r.config.data)},6424:function(e,t){"use strict";var n=this&&this.__assign||function(){return n=Object.assign||function(e){for(var t,n=1,o=arguments.length;n<o;n++)for(var i in t=arguments[n])Object.prototype.hasOwnProperty.call(t,i)&&(e[i]=t[i]);return e},n.apply(this,arguments)};t.__esModule=!0,t.eventHooks=void 0;var o=function(e){return Array.isArray(e)?e:[e]},i=function(e,t){var n=null,o=[];return n=t.includes(".")?(o=t.split(".")).shift():t,void 0!==e&&(!(0!==o.length||!Object.prototype.hasOwnProperty.call(e,n))||void 0!==n&&i(e[n],o.join(".")))},r={events:{},subs:{},currentId:0};t.eventHooks=r;r.listenSetState=function(e,t,n,r){var a;void 0===r&&(r=!0),a="string"==typeof e?document.querySelector(e):e,t=o(t),a&&a.eventHooks&&t.forEach((function(e){i(null==a?void 0:a.eventHooks,"events.".concat(e,".").concat(n,".active"))&&(a.eventHooks.events[e][n].active=r)}))},r.unlisten=function(e,t,n){if(!r.events[n])return!1;var i;if(t=o(t),i="string"==typeof e?document.querySelector(e):e,r.events[n]&&i.eventHooks&&i.eventHooks.events&&i.eventHooks.hasCallback&&i.uniqueHookId>=0){var a="".concat(i.tagName,"_").concat(i.uniqueHookId);t.forEach((function(e){if(r.events[n][a][e]&&delete r.events[n][a][e],i.eventHooks.events[e]&&i.eventHooks.events[e][n]){var t=i.eventHooks.events[e][n].callbackHandler;i.eventHooks&&i.eventHooks.hasCallback[e]&&t&&(i.removeEventListener(e,t,{capture:!0}),delete i.eventHooks.hasCallback[e],delete i.eventHooks.events[e][n])}}))}if(i.eventHooks&&i.eventHooks.events)t.forEach((function(e){Object.prototype.hasOwnProperty.call(i.eventHooks.events,e)&&Object.prototype.hasOwnProperty.call(i.eventHooks.events[e],n)&&delete i.eventHooks.events[e][n]}));else if(!i.eventHooks)throw new Error("Unlisten was attempted on an uninitialized item.")},r.listen=function(e,t,a,s,l){var c;void 0===l&&(l={}),t=o(t),(c="string"==typeof e?document.querySelector(e):e).uniqueHookId||(c.uniqueHookId=function(){var e=r.currentId;return r.currentId++,e}());var u="".concat(c.tagName,"_").concat(c.uniqueHookId);Object.prototype.hasOwnProperty.call(r.events,a)||(r.events[a]={}),Object.prototype.hasOwnProperty.call(r.events[a],u)||(r.events[a][u]={}),c.eventHooks||(c.eventHooks={events:{},hasCallback:{}}),t.forEach((function(t){Object.prototype.hasOwnProperty.call(r.events[a][u],t)||(r.events[a][u][t]={callbacks:[]}),r.events[a][u][t].callbacks.includes(s)||r.events[a][u][t].callbacks.push(s),Object.prototype.hasOwnProperty.call(c.eventHooks.events,t)||(c.eventHooks.events[t]={});if(c.eventHooks.events[t][a]={active:!0,callbackHandler:null},!c.eventHooks.hasCallback[t]){var o=function(n){!function(e){var t=[e.type,e.currentTarget],n=t[0],o=t[1];o&&o.eventHooks&&o.eventHooks.events[n]&&Object.keys(o.eventHooks.events[n]).forEach((function(t){if(o.eventHooks.events[n][t].active){var a="".concat(o.tagName,"_").concat(o.uniqueHookId);i(r.events,"".concat(t,".").concat(a,".").concat(n,".callbacks"))&&r.events[t][a][n].callbacks.forEach((function(t){return t(e)}))}}))}(n),!0===l.destroy&&r.unlisten(e,t,a)};c.addEventListener(t,o,n({capture:!0},l.options||{})),c.eventHooks.events[t][a].callbackHandler=o,c.eventHooks.hasCallback[t]=!0}})),l.onAdd&&l.onAdd(c,t,a)},r.subscribe=function(e,t,n){Object.prototype.hasOwnProperty.call(r.subs,e)||(r.subs[e]={}),r.subs[e][t]=n},r.unsubscribe=function(e,t){Object.prototype.hasOwnProperty.call(r.subs,e)&&Object.prototype.hasOwnProperty.call(r.subs[e],t)&&delete r.subs[e][t]},r.trigger=function(e){for(var t=[],n=1;n<arguments.length;n++)t[n-1]=arguments[n];Object.prototype.hasOwnProperty.call(r.subs,e)&&Object.keys(r.subs[e]).forEach((function(n){var o;"function"==typeof r.subs[e][n]&&(o=r.subs[e])[n].apply(o,t)}))}},9879:function(e,t,n){"use strict";var o=this&&this.__spreadArray||function(e,t,n){if(n||2===arguments.length)for(var o,i=0,r=t.length;i<r;i++)!o&&i in t||(o||(o=Array.prototype.slice.call(t,0,i)),o[i]=t[i]);return e.concat(o||Array.prototype.slice.call(t))};t.__esModule=!0,t.log=void 0;var i=n(8598).config.get("debug");t.log=function(e){for(var t=[],n=1;n<arguments.length;n++)t[n-1]=arguments[n];i&&console.log.apply(console,o(["[".concat(e.toUpperCase(),"]")],t,!1))}},8834:(e,t)=>{"use strict";t.__esModule=!0,t.formatDate=void 0;t.formatDate=function(e,t){var n,o,i=["Sun","Mon","Tues","Wednes","Thurs","Fri","Satur","January","February","March","April","May","June","July","August","September","October","November","December"],r=/\\?(.?)/gi,a=function(e,t){return o[e]?o[e]():t},s=function(e,t){for(e=String(e);e.length<t;)e="0"+e;return e};o={d:function(){return s(o.j(),2)},D:function(){return o.l().slice(0,3)},j:function(){return n.getDate()},l:function(){return i[o.w()]+"day"},N:function(){return o.w()||7},S:function(){var e=o.j(),t=e%10;return t<=3&&1===parseInt((e%100/10).toString(),10)&&(t=0),["st","nd","rd"][t-1]||"th"},w:function(){return n.getDay()},z:function(){var e=new Date(o.Y(),o.n()-1,o.j()),t=new Date(o.Y(),0,1);return Math.round((e-t)/864e5)},W:function(){var e=new Date(o.Y(),o.n()-1,o.j()-o.N()+3),t=new Date(e.getFullYear(),0,4);return s(1+Math.round((e-t)/864e5/7),2)},F:function(){return i[6+o.n()]},m:function(){return s(o.n(),2)},M:function(){return o.F().slice(0,3)},n:function(){return n.getMonth()+1},t:function(){return new Date(o.Y(),o.n(),0).getDate()},L:function(){var e=o.Y();return e%4==0&&e%100!=0||e%400==0},o:function(){var e=o.n(),t=o.W();return o.Y()+(12===e&&t<9?1:1===e&&t>9?-1:0)},Y:function(){return n.getFullYear()},y:function(){return o.Y().toString().slice(-2)},a:function(){return n.getHours()>11?"pm":"am"},A:function(){return o.a().toUpperCase()},B:function(){var e=3600*n.getUTCHours(),t=60*n.getUTCMinutes(),o=n.getUTCSeconds();return s(Math.floor((e+t+o+3600)/86.4)%1e3,3)},g:function(){return o.G()%12||12},G:function(){return n.getHours()},h:function(){return s(o.g(),2)},H:function(){return s(o.G(),2)},i:function(){return s(n.getMinutes(),2)},s:function(){return s(n.getSeconds(),2)},u:function(){return s(1e3*n.getMilliseconds(),6)},e:function(){throw new Error("Not supported (see source code of date() for timezone on how to add support)")},I:function(){return new Date(o.Y(),0)-Date.UTC(o.Y(),0)!=new Date(o.Y(),6)-Date.UTC(o.Y(),6)?1:0},O:function(){var e=n.getTimezoneOffset(),t=Math.abs(e);return(e>0?"-":"+")+s(100*Math.floor(t/60)+t%60,4)},P:function(){var e=o.O();return e.substr(0,3)+":"+e.substr(3,2)},T:function(){return"UTC"},Z:function(){return 60*-n.getTimezoneOffset()},c:function(){return"Y-m-d\\TH:i:sP".replace(r,a)},r:function(){return"D, d M Y H:i:s O".replace(r,a)},U:function(){return n/1e3|0}};return function(e,t){return n=void 0===t?new Date:t instanceof Date?new Date(t):new Date(1e3*t),e.replace(r,a)}(e,t)}},3230:(e,t,n)=>{"use strict";t.__esModule=!0,t.mouseleave=t.mouseenter=t.mousemove=void 0;var o=n(32);function i(e){this.data.offset={x:e.clientX,y:e.clientY}}function r(e){var t=e.target;if(Object.prototype.hasOwnProperty.call(this.options,"source")&&this.options.source?this.data.src=this.options.source:t.hasAttribute("data-src")?this.data.src=t.getAttribute("data-src"):t.hasAttribute("src")?this.data.src=t.getAttribute("src"):t.hasAttribute("href")&&(this.data.src=t.getAttribute("href")),null===this.data.src)throw Error("No valid source value found.");if(this.data.type=o.getType.call(this),null!=this.data.type){var n=this;this.data.left=this.data.offset.x<=window.innerWidth/2;var i=(0,o.createContainer)();document.body.prepend(i),this.options.cursor&&null===this.data.cursor&&(this.data.cursor=t.style.cursor,t.style.cursor="progress"),0!==this.data.type&&1!==this.data.type||(0===this.data.type?o.loadImage:o.loadVideo).call(this,this.data.src,(function(e,o){e?(i.appendChild(e),n.data.container=i,n.data.dimensions={x:o[0],y:o[1]},n.loaded=!0,a.call(n),i.style.visibility="visible",n.options.cursor&&(t.style.cursor=n.data.cursor?n.data.cursor:"")):n.options.cursor&&(t.style.cursor=n.data.cursor?n.data.cursor:"")}))}}function a(){this.updater(this.data.left,this.data.container,{dimensions:this.data.dimensions,offset:{x:this.data.offset.x,y:this.data.offset.y}})}t.mousemove=function(e){if(i.call(this,e),!this.loaded)return!1;a.call(this)},t.mouseenter=function(e){this.active=!0;var t=parseInt(this.id),n=this;i.call(this,e),this.options.delay&&this.options.delay>0?this.timers.delay=setTimeout((function(){n.active&&t===n.id&&r.call(n,e)}),this.options.delay):r.call(n,e)},t.mouseleave=function(e){var t=null;this.active=!1,this.id++,this.currentElement&&("VIDEO"===this.currentElement.tagName&&(t=this.currentElement.currentTime,this.currentElement.pause(),this.currentElement.muted=!0,this.currentElement.onloadeddata=function(){},this.currentElement.onloadedmetadata=function(){}),this.currentElement.remove()),this.options.cursor&&"progress"===e.target.style.cursor&&(e.target.style.cursor=this.data.cursor?this.data.cursor:"",this.data.cursor=null);var n=document.querySelector(".preview-container");if(n&&n.remove(),clearTimeout(this.timers.delay),clearInterval(this.timers.load),this.loaded=!1,this.data.on.hasOwnProperty("onLoaded"))try{this.data.on.onLoaded({loaded:!1,type:null,audible:!1,element:null,timestamp:t,src:this.data.src})}catch(e){console.error(e)}}},4933:function(e,t,n){"use strict";var o=this&&this.__assign||function(){return o=Object.assign||function(e){for(var t,n=1,o=arguments.length;n<o;n++)for(var i in t=arguments[n])Object.prototype.hasOwnProperty.call(t,i)&&(e[i]=t[i]);return e},o.apply(this,arguments)};t.__esModule=!0;var i=n(32),r=n(3230),a={delay:75,encodeAll:!1,cursor:!0,force:null},s=function(){function e(e,t){if(void 0===t&&(t={}),!e)throw Error("No element were passed.");this.element=e,this.options=t,l.call(this)}return e.prototype.reload=function(){this.destroy(),l.call(this)},e.prototype.destroy=function(){var e=this.events;this.handle.removeEventListener("mouseenter",e.mouseenter,!1),this.handle.removeEventListener("mouseleave",e.mouseleave,!1),this.handle.removeEventListener("mousemove",e.mousemove,!1)},e}();function l(){this.options=o(o({},a),this.options),this.data={cursor:null,left:null,src:null,type:null,offset:null,dimensions:null,force:null},this.data.on={},"object"==typeof this.options.on&&null!==this.options.on&&(this.data.on=this.options.on),this.options.force&&(this.data.force=this.options.force),this.timers={load:null,delay:null},this.handle=this.element,this.updater=(0,i.getMove)(),this.events={mouseenter:r.mouseenter.bind(this),mouseleave:r.mouseleave.bind(this),mousemove:r.mousemove.bind(this)},this.active=!1,this.id=0,this.handle.addEventListener("mouseleave",this.events.mouseleave,!1),this.handle.addEventListener("mouseenter",this.events.mouseenter,!1),this.handle.addEventListener("mousemove",this.events.mousemove,!1)}t.default=function(e,t){return new s(e,t)}},32:(e,t)=>{"use strict";function n(e,t,n){var o=n.offset,i=n.dimensions;return t.style.left=function(e,t,n){return e?window.innerWidth-n-20>t?n+20:window.innerWidth>t?window.innerWidth-t:0:t<n-20?n-t-20:0}(e,t.clientWidth,o.x)+"px",t.style.top=function(e,t){var n=window.innerHeight;if(t.y>=n)return 0;var o=e.y/n*100;return n/100*(o=o>100?100:o)-t.y/100*o}(o,i)+"px",!1}function o(e){return this.options.encodeAll?e.replace("#","%23").replace("?","%3F"):encodeURI(e)}function i(e){return void 0!==e.webkitAudioDecodedByteCount?e.webkitAudioDecodedByteCount>0:void 0!==e.mozHasAudio?!!e.mozHasAudio:void 0!==e.audioTracks&&!(!e.audioTracks||!e.audioTracks.length)}t.__esModule=!0,t.loadVideo=t.loadImage=t.createContainer=t.getType=t.getMove=void 0,t.getMove=function(){return window.requestAnimationFrame?function(e,t,o){window.requestAnimationFrame((function(){n(e,t,o)}))}:function(e,t,o){n(e,t,o)}},t.getType=function(){return this.data.force?(this.data.extension=this.data.force.extension,this.data.force.type):(this.data.extension=this.data.src.split(".").pop().toLowerCase(),["jpg","jpeg","gif","png","ico","svg","bmp","webp"].includes(this.data.extension)?0:["webm","mp4","ogg","ogv","mov"].includes(this.data.extension)?1:null)},t.createContainer=function(){var e=document.createElement("div");e.className="preview-container";var t={"pointer-events":"none",position:"fixed",visibility:"hidden","z-index":"9999",top:"-9999px",left:"-9999px","max-width":"100vw","max-height":"100vh"};return Object.keys(t).forEach((function(n){e.style[n]=t[n]})),e},t.loadImage=function(e,t){var n=this,i=document.createElement("img");this.currentElement=i,i.style["max-width"]="inherit",i.style["max-height"]="inherit",i.src=o.call(n,e),n.timers.load=setInterval((function(){if(n.active){var o=i.naturalWidth,r=i.naturalHeight;if(o&&r){if(n.data.on.hasOwnProperty("onLoaded"))try{n.data.on.onLoaded({loaded:!0,type:"IMAGE",audible:!1,element:i,src:e})}catch(e){console.error(e)}clearInterval(n.timers.load),t(i,[o,r])}}else t(!1)}),30)},t.loadVideo=function(e,t){var n=this,r=document.createElement("video"),a=r.appendChild(document.createElement("source"));this.currentElement=r,["muted","loop","autoplay"].forEach((function(e){r[e]=!0})),a.type="video/"+("mov"===this.data.extension?"mp4":"ogv"===this.data.extension?"ogg":this.data.extension),a.src=o.call(this,e),r.style["max-width"]="inherit",r.style["max-height"]="inherit",r.onloadeddata=function(){if(n.active){if(n.data.on.hasOwnProperty("onLoaded"))try{n.data.on.onLoaded({loaded:!0,type:"VIDEO",audible:i(r),element:r,src:e})}catch(e){console.error(e)}}else t(!1)},r.onloadedmetadata=function(e){var o=e.target;n.active?t(r,[o.videoWidth,o.videoHeight]):t(!1)}}},6866:(e,t,n)=>{"use strict";function o(e){return o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},o(e)}Object.defineProperty(t,"__esModule",{value:!0});var i={};t.default=void 0;var r=function(e,t){if(!t&&e&&e.__esModule)return e;if(null===e||"object"!==o(e)&&"function"!=typeof e)return{default:e};var n=s(t);if(n&&n.has(e))return n.get(e);var i={},r=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var a in e)if("default"!==a&&Object.prototype.hasOwnProperty.call(e,a)){var l=r?Object.getOwnPropertyDescriptor(e,a):null;l&&(l.get||l.set)?Object.defineProperty(i,a,l):i[a]=e[a]}i.default=e,n&&n.set(e,i);return i}(n(4387)),a=n(145);function s(e){if("function"!=typeof WeakMap)return null;var t=new WeakMap,n=new WeakMap;return(s=function(e){return e?n:t})(e)}function l(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function c(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}Object.keys(a).forEach((function(e){"default"!==e&&"__esModule"!==e&&(Object.prototype.hasOwnProperty.call(i,e)||e in t&&t[e]===a[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return a[e]}}))}));var u=function(){function e(t){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),c(this,"state",void 0),c(this,"props",void 0),this.state=r.getInitialState(),this.props=r.getInitialProps(t),this.handleSwipeStart=this.handleSwipeStart.bind(this),this.handleSwipeMove=this.handleSwipeMove.bind(this),this.handleSwipeEnd=this.handleSwipeEnd.bind(this),this.handleMouseDown=this.handleMouseDown.bind(this),this.handleMouseMove=this.handleMouseMove.bind(this),this.handleMouseUp=this.handleMouseUp.bind(this),this.handleMouseLeave=this.handleMouseLeave.bind(this)}var t,n,o;return t=e,n=[{key:"init",value:function(){this.setupTouchListeners(),this.setupMouseListeners()}},{key:"update",value:function(e){var t=this.props,n=Object.assign({},t,e);if(t.element!==n.element||t.target!==n.target)return this.destroy(),this.props=n,void this.init();this.props=n,t.mouseTrackingEnabled===n.mouseTrackingEnabled&&t.preventTrackingOnMouseleave===n.preventTrackingOnMouseleave||(this.cleanupMouseListeners(),n.mouseTrackingEnabled?this.setupMouseListeners():this.cleanupMouseListeners()),t.touchTrackingEnabled!==n.touchTrackingEnabled&&(this.cleanupTouchListeners(),n.touchTrackingEnabled?this.setupTouchListeners():this.cleanupTouchListeners())}},{key:"destroy",value:function(){this.cleanupMouseListeners(),this.cleanupTouchListeners(),this.state=r.getInitialState(),this.props=r.getInitialProps()}},{key:"setupTouchListeners",value:function(){var e=this.props,t=e.element,n=e.target,o=e.touchTrackingEnabled;if(t&&o){var i=n||t,a=r.checkIsPassiveSupported(),s=r.getOptions(a);i.addEventListener("touchstart",this.handleSwipeStart,s),i.addEventListener("touchmove",this.handleSwipeMove,s),i.addEventListener("touchend",this.handleSwipeEnd,s)}}},{key:"cleanupTouchListeners",value:function(){var e=this.props,t=e.element,n=e.target||t;n&&(n.removeEventListener("touchstart",this.handleSwipeStart),n.removeEventListener("touchmove",this.handleSwipeMove),n.removeEventListener("touchend",this.handleSwipeEnd))}},{key:"setupMouseListeners",value:function(){var e=this.props,t=e.element,n=e.mouseTrackingEnabled,o=e.preventTrackingOnMouseleave;n&&t&&(t.addEventListener("mousedown",this.handleMouseDown),t.addEventListener("mousemove",this.handleMouseMove),t.addEventListener("mouseup",this.handleMouseUp),o&&t.addEventListener("mouseleave",this.handleMouseLeave))}},{key:"cleanupMouseListeners",value:function(){var e=this.props.element;e&&(e.removeEventListener("mousedown",this.handleMouseDown),e.removeEventListener("mousemove",this.handleMouseMove),e.removeEventListener("mouseup",this.handleMouseUp),e.removeEventListener("mouseleave",this.handleMouseLeave))}},{key:"getEventData",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{directionDelta:0},n=this.props.rotationAngle,o=t.directionDelta,i=r.calculateMovingPosition(e),a=r.rotateByAngle(i,n);return r.calculatePosition(this.state,{rotatePosition:a,directionDelta:o})}},{key:"handleSwipeStart",value:function(e){if(!r.checkIsMoreThanSingleTouches(e)){var t=this.props.rotationAngle,n=r.calculateMovingPosition(e),o=r.rotateByAngle(n,t),i=o.x,a=o.y;this.state=r.getInitialState({isSwiping:!1,start:Date.now(),x:i,y:a})}}},{key:"handleSwipeMove",value:function(e){var t=this.state,n=t.x,o=t.y,i=t.isSwiping;if(n&&o&&!r.checkIsMoreThanSingleTouches(e)){var a=this.props.directionDelta||0,s=this.getEventData(e,{directionDelta:a}),l=s.absX,c=s.absY,u=s.deltaX,d=s.deltaY,p=s.directionX,f=s.directionY,v=s.duration,h=s.velocity,y=this.props,g=y.delta,m=y.preventDefaultTouchmoveEvent,b=y.onSwipeStart,w=y.onSwiping;e.cancelable&&m&&e.preventDefault(),l<Number(g)&&c<Number(g)&&!i||(b&&!i&&b(e,{deltaX:u,deltaY:d,absX:l,absY:c,directionX:p,directionY:f,duration:v,velocity:h}),this.state.isSwiping=!0,w&&w(e,{deltaX:u,deltaY:d,absX:l,absY:c,directionX:p,directionY:f,duration:v,velocity:h}))}}},{key:"handleSwipeEnd",value:function(e){var t=this.props,n=t.onSwiped,o=t.onTap;if(this.state.isSwiping){var i=this.props.directionDelta||0,a=this.getEventData(e,{directionDelta:i});n&&n(e,a)}else{var s=this.getEventData(e);o&&o(e,s)}this.state=r.getInitialState()}},{key:"handleMouseDown",value:function(e){var t=this.props.target;t?t===e.target&&this.handleSwipeStart(e):this.handleSwipeStart(e)}},{key:"handleMouseMove",value:function(e){this.handleSwipeMove(e)}},{key:"handleMouseUp",value:function(e){var t=this.state.isSwiping,n=this.props.target;n?(n===e.target||t)&&this.handleSwipeEnd(e):this.handleSwipeEnd(e)}},{key:"handleMouseLeave",value:function(e){this.state.isSwiping&&this.handleSwipeEnd(e)}}],o=[{key:"isTouchEventsSupported",value:function(){return r.checkIsTouchEventsSupported()}}],n&&l(t.prototype,n),o&&l(t,o),Object.defineProperty(t,"prototype",{writable:!1}),e}();t.default=u},145:(e,t)=>{"use strict";var n,o,i;Object.defineProperty(t,"__esModule",{value:!0}),t.TraceDirectionKey=t.Direction=t.Axis=void 0,t.TraceDirectionKey=n,function(e){e.NEGATIVE="NEGATIVE",e.POSITIVE="POSITIVE",e.NONE="NONE"}(n||(t.TraceDirectionKey=n={})),t.Direction=o,function(e){e.TOP="TOP",e.LEFT="LEFT",e.RIGHT="RIGHT",e.BOTTOM="BOTTOM",e.NONE="NONE"}(o||(t.Direction=o={})),t.Axis=i,function(e){e.X="x",e.Y="y"}(i||(t.Axis=i={}))},7394:(e,t,n)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.calculateDirection=function(e){var t,n=o.TraceDirectionKey.NEGATIVE,i=o.TraceDirectionKey.POSITIVE,r=e[e.length-1],a=e[e.length-2]||0;if(e.every((function(e){return 0===e})))return o.TraceDirectionKey.NONE;t=r>a?i:n,0===r&&(t=a<0?i:n);return t};var o=n(145)},558:(e,t,n)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.calculateDirectionDelta=function(e){for(var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0,n=e.length,r=n-1,a=o.TraceDirectionKey.NONE;r>=0;r--){var s=e[r],l=(0,i.getDirectionKey)(s),c=(0,i.getDirectionValue)(s[l]),u=e[r-1]||{},d=(0,i.getDirectionKey)(u),p=(0,i.getDirectionValue)(u[d]),f=(0,i.getDifference)(c,p);if(f>=t){a=l;break}a=d}return a};var o=n(145),i=n(4387)},5963:(e,t)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.calculateDuration=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0;return e?t-e:0}},9111:(e,t)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.calculateMovingPosition=function(e){if("changedTouches"in e){var t=e.changedTouches&&e.changedTouches[0];return{x:t&&t.clientX,y:t&&t.clientY}}return{x:e.clientX,y:e.clientY}}},9405:(e,t,n)=>{"use strict";function o(e){return o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},o(e)}Object.defineProperty(t,"__esModule",{value:!0}),t.calculatePosition=function(e,t){var n=e.start,o=e.x,a=e.y,s=e.traceX,l=e.traceY,c=t.rotatePosition,u=t.directionDelta,d=c.x-o,p=a-c.y,f=Math.abs(d),v=Math.abs(p);i.updateTrace(s,d),i.updateTrace(l,p);var h=i.resolveDirection(s,r.Axis.X,u),y=i.resolveDirection(l,r.Axis.Y,u),g=i.calculateDuration(n,Date.now()),m=i.calculateVelocity(f,v,g);return{absX:f,absY:v,deltaX:d,deltaY:p,directionX:h,directionY:y,duration:g,positionX:c.x,positionY:c.y,velocity:m}};var i=function(e,t){if(!t&&e&&e.__esModule)return e;if(null===e||"object"!==o(e)&&"function"!=typeof e)return{default:e};var n=a(t);if(n&&n.has(e))return n.get(e);var i={},r=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var s in e)if("default"!==s&&Object.prototype.hasOwnProperty.call(e,s)){var l=r?Object.getOwnPropertyDescriptor(e,s):null;l&&(l.get||l.set)?Object.defineProperty(i,s,l):i[s]=e[s]}i.default=e,n&&n.set(e,i);return i}(n(4387)),r=n(145);function a(e){if("function"!=typeof WeakMap)return null;var t=new WeakMap,n=new WeakMap;return(a=function(e){return e?n:t})(e)}},658:(e,t,n)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.calculateTraceDirections=function(){for(var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],t=[],n=o.TraceDirectionKey.POSITIVE,r=o.TraceDirectionKey.NEGATIVE,a=0,s=[],l=o.TraceDirectionKey.NONE;a<e.length;a++){var c=e[a],u=e[a-1];if(s.length){var d=c>u?n:r;l===o.TraceDirectionKey.NONE&&(l=d),d===l?s.push(c):(t.push(i({},l,s.slice())),(s=[]).push(c),l=d)}else 0!==c&&(l=c>0?n:r),s.push(c)}s.length&&t.push(i({},l,s));return t};var o=n(145);function i(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}},5787:(e,t)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.calculateVelocity=function(e,t,n){return Math.sqrt(e*e+t*t)/(n||1)}},3368:(e,t)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.checkIsMoreThanSingleTouches=void 0;t.checkIsMoreThanSingleTouches=function(e){return Boolean(e.touches&&e.touches.length>1)}},5566:(e,t,n)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.checkIsPassiveSupported=function(e){if("boolean"==typeof e)return e;var t={isPassiveSupported:e};try{var n=(0,o.createOptions)(t);window.addEventListener("checkIsPassiveSupported",i,n),window.removeEventListener("checkIsPassiveSupported",i,n)}catch(e){}return t.isPassiveSupported},t.noop=void 0;var o=n(1890);var i=function(){};t.noop=i},7053:(e,t)=>{"use strict";function n(e){return n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},n(e)}Object.defineProperty(t,"__esModule",{value:!0}),t.checkIsTouchEventsSupported=void 0;t.checkIsTouchEventsSupported=function(){return"object"===("undefined"==typeof window?"undefined":n(window))&&("ontouchstart"in window||Boolean(window.navigator.maxTouchPoints))}},2309:(e,t,n)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.resolveAxisDirection=t.getDirectionValue=t.getDirectionKey=t.getDifference=void 0;var o=n(145);t.getDirectionKey=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},t=Object.keys(e).toString();switch(t){case o.TraceDirectionKey.POSITIVE:return o.TraceDirectionKey.POSITIVE;case o.TraceDirectionKey.NEGATIVE:return o.TraceDirectionKey.NEGATIVE;default:return o.TraceDirectionKey.NONE}};t.getDirectionValue=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[];return e[e.length-1]||0};t.getDifference=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0;return Math.abs(e-t)};t.resolveAxisDirection=function(e,t){var n=o.Direction.LEFT,i=o.Direction.RIGHT,r=o.Direction.NONE;return e===o.Axis.Y&&(n=o.Direction.BOTTOM,i=o.Direction.TOP),t===o.TraceDirectionKey.NEGATIVE&&(r=n),t===o.TraceDirectionKey.POSITIVE&&(r=i),r}},1890:(e,t)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.createOptions=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};return Object.defineProperty(e,"passive",{get:function(){return this.isPassiveSupported=!0,!0},enumerable:!0}),e}},950:(e,t)=>{"use strict";function n(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);t&&(o=o.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,o)}return n}function o(e){for(var t=1;t<arguments.length;t++){var o=null!=arguments[t]?arguments[t]:{};t%2?n(Object(o),!0).forEach((function(t){i(e,t,o[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(o)):n(Object(o)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(o,t))}))}return e}function i(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}Object.defineProperty(t,"__esModule",{value:!0}),t.getInitialProps=void 0;t.getInitialProps=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};return o({element:null,target:null,delta:10,directionDelta:0,rotationAngle:0,mouseTrackingEnabled:!1,touchTrackingEnabled:!0,preventDefaultTouchmoveEvent:!1,preventTrackingOnMouseleave:!1},e)}},4521:(e,t)=>{"use strict";function n(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);t&&(o=o.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,o)}return n}function o(e){for(var t=1;t<arguments.length;t++){var o=null!=arguments[t]?arguments[t]:{};t%2?n(Object(o),!0).forEach((function(t){i(e,t,o[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(o)):n(Object(o)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(o,t))}))}return e}function i(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}Object.defineProperty(t,"__esModule",{value:!0}),t.getInitialState=void 0;t.getInitialState=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};return o({x:0,y:0,start:0,isSwiping:!1,traceX:[],traceY:[]},e)}},1662:(e,t)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.getOptions=function(){var e=arguments.length>0&&void 0!==arguments[0]&&arguments[0];if(e)return{passive:!1};return{}}},4387:(e,t,n)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0});var o=n(7394);Object.keys(o).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===o[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return o[e]}}))}));var i=n(558);Object.keys(i).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===i[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return i[e]}}))}));var r=n(5963);Object.keys(r).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===r[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return r[e]}}))}));var a=n(9111);Object.keys(a).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===a[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return a[e]}}))}));var s=n(9405);Object.keys(s).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===s[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return s[e]}}))}));var l=n(658);Object.keys(l).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===l[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return l[e]}}))}));var c=n(5787);Object.keys(c).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===c[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return c[e]}}))}));var u=n(3368);Object.keys(u).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===u[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return u[e]}}))}));var d=n(5566);Object.keys(d).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===d[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return d[e]}}))}));var p=n(7053);Object.keys(p).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===p[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return p[e]}}))}));var f=n(2309);Object.keys(f).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===f[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return f[e]}}))}));var v=n(1890);Object.keys(v).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===v[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return v[e]}}))}));var h=n(4521);Object.keys(h).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===h[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return h[e]}}))}));var y=n(950);Object.keys(y).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===y[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return y[e]}}))}));var g=n(1662);Object.keys(g).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===g[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return g[e]}}))}));var m=n(5315);Object.keys(m).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===m[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return m[e]}}))}));var b=n(7953);Object.keys(b).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===b[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return b[e]}}))}));var w=n(9958);Object.keys(w).forEach((function(e){"default"!==e&&"__esModule"!==e&&(e in t&&t[e]===w[e]||Object.defineProperty(t,e,{enumerable:!0,get:function(){return w[e]}}))}))},5315:(e,t,n)=>{"use strict";function o(e){return o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},o(e)}Object.defineProperty(t,"__esModule",{value:!0}),t.resolveDirection=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:r.Axis.X,n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:0;if(n){var o=i.calculateTraceDirections(e),a=i.calculateDirectionDelta(o,n);return i.resolveAxisDirection(t,a)}var s=i.calculateDirection(e);return i.resolveAxisDirection(t,s)};var i=function(e,t){if(!t&&e&&e.__esModule)return e;if(null===e||"object"!==o(e)&&"function"!=typeof e)return{default:e};var n=a(t);if(n&&n.has(e))return n.get(e);var i={},r=Object.defineProperty&&Object.getOwnPropertyDescriptor;for(var s in e)if("default"!==s&&Object.prototype.hasOwnProperty.call(e,s)){var l=r?Object.getOwnPropertyDescriptor(e,s):null;l&&(l.get||l.set)?Object.defineProperty(i,s,l):i[s]=e[s]}i.default=e,n&&n.set(e,i);return i}(n(4387)),r=n(145);function a(e){if("function"!=typeof WeakMap)return null;var t=new WeakMap,n=new WeakMap;return(a=function(e){return e?n:t})(e)}},7953:(e,t)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.rotateByAngle=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:0;if(0===t)return e;var n=e.x,o=e.y,i=Math.PI/180*t,r=n*Math.cos(i)+o*Math.sin(i),a=o*Math.cos(i)-n*Math.sin(i);return{x:r,y:a}}},9958:(e,t)=>{"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.updateTrace=function(e,t){e[e.length-1]!==t&&e.push(t);return e}},2515:(e,t,n)=>{"use strict";n.r(t),function(e,t,n,o){var i=[],r={_version:"3.11.7",_config:{classPrefix:"",enableClasses:!0,enableJSClass:!0,usePrefixes:!0},_q:[],on:function(e,t){var n=this;setTimeout((function(){t(n[e])}),0)},addTest:function(e,t,n){i.push({name:e,fn:t,options:n})},addAsyncTest:function(e){i.push({name:null,fn:e})}},a=function(){};a.prototype=r,a=new a;var s=[];var l=n.documentElement,c="svg"===l.nodeName.toLowerCase();function u(){return"function"!=typeof n.createElement?n.createElement(arguments[0]):c?n.createElementNS.call(n,"http://www.w3.org/2000/svg",arguments[0]):n.createElement.apply(n,arguments)}function d(e,t,o,i){var r,a,s,d,p="modernizr",f=u("div"),v=function(){var e=n.body;return e||((e=u(c?"svg":"body")).fake=!0),e}();if(parseInt(o,10))for(;o--;)(s=u("div")).id=i?i[o]:p+(o+1),f.appendChild(s);return(r=u("style")).type="text/css",r.id="s"+p,(v.fake?v:f).appendChild(r),v.appendChild(f),r.styleSheet?r.styleSheet.cssText=e:r.appendChild(n.createTextNode(e)),f.id=p,v.fake&&(v.style.background="",v.style.overflow="hidden",d=l.style.overflow,l.style.overflow="hidden",l.appendChild(v)),a=t(f,e),v.fake&&v.parentNode?(v.parentNode.removeChild(v),l.style.overflow=d,l.offsetHeight):f.parentNode.removeChild(f),!!a}var p,f=(p=t.matchMedia||t.msMatchMedia)?function(e){var t=p(e);return t&&t.matches||!1}:function(e){var n=!1;return d("@media "+e+" { #modernizr { position: absolute; } }",(function(e){n="absolute"===function(e,n,o){var i;if("getComputedStyle"in t){i=getComputedStyle.call(t,e,n);var r=t.console;null!==i?o&&(i=i.getPropertyValue(o)):r&&r[r.error?"error":"log"].call(r,"getComputedStyle returning null, its possible modernizr test results are inaccurate")}else i=!n&&e.currentStyle&&e.currentStyle[o];return i}(e,null,"position")})),n};r.mq=f,function(){var e,t,n,o,r,l;for(var c in i)if(i.hasOwnProperty(c)){if(e=[],(t=i[c]).name&&(e.push(t.name.toLowerCase()),t.options&&t.options.aliases&&t.options.aliases.length))for(n=0;n<t.options.aliases.length;n++)e.push(t.options.aliases[n].toLowerCase());for(o=typeof t.fn==="function"?t.fn():t.fn,r=0;r<e.length;r++)1===(l=e[r].split(".")).length?a[l[0]]=o:(a[l[0]]&&(!a[l[0]]||a[l[0]]instanceof Boolean)||(a[l[0]]=new Boolean(a[l[0]])),a[l[0]][l[1]]=o),s.push((o?"":"no-")+l.join("-"))}}(),delete r.addTest,delete r.addAsyncTest;for(var v=0;v<a._q.length;v++)a._q[v]();e.Modernizr=a}(window,window,document)},564:e=>{"use strict";e.exports=JSON.parse('{"settingsLabels":{"stylingCompact":{"text":"Compact Style","description":"Set the page to use a more compact style."},"galleryListAlignment":{"text":"List Alignment"},"galleryReverseSearch":{"text":"Reverse Search","description":"Toggle the visibility of reverse search options on images."},"galleryVideoAutoplay":{"text":"Autoplay Videos","description":"Toggle the autoplaying of videos."},"galleryFitContent":{"text":"Fit Content","description":"Force images and videos to fill the screen."}},"menuLabels":{"filter":{"text":"[Show] Filter"},"wget":{"text":"[Copy] WGET"},"gallery":{"text":"[Open] Gallery"},"settings":{"text":"[Open] Settings"}},"reverseSearch":{"Google":"https://lens.google.com/uploadbyurl?url={URL}","Yandex":"https://yandex.com/images/search?rpt=imageview&url={URL}","Bing":"https://bing.com/images/search?q=imgurl:{URL}&view=detailv2&iss=sbi#enterInsights","SauceNAO":"https://saucenao.com/search.php?url={URL}"}}')}},t={};function n(o){var i=t[o];if(void 0!==i)return i.exports;var r=t[o]={exports:{}};return e[o].call(r.exports,r,r.exports,n),r.exports}n.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};n(3937)})();</script>
<?=$getInjectable('footer');?>
</body>
</html>