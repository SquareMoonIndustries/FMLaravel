<?php namespace FMLaravel\Database;

use Illuminate\Database\Connection as BaseConnection;
//use airmoi\FileMaker\FileMaker;
use Illuminate\Support\Str;
use \Session;
use FMLaravel\Database\LogFacade;
require_once __DIR__.'/../fmPDA/v2/fmPDA.php';
class Connection extends BaseConnection
{

    /** List of instantiated connections
     * @var array
     */
    protected $connections = [];

    public function __construct(array $config)
    {
        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();

        $this->config = $config;
    }

    public function filemaker($type = 'default', $configMutator = null)
    {
        $config = $this->config;

        // if neither a particular configuration nor a configuration mutator exists, just take default connection
        if ((!array_key_exists($type, $config) || !is_array($config[$type])) && !is_callable($configMutator)) {
            $type = 'default';
        }

        // has it already been created?
        if (isset($this->connections[$type])) {
            return $this->connections[$type];
        }


        // if any particular configuration is wanted and defined load it
        if (array_key_exists($type, $config) && is_array($config[$type])) {
            $config = array_merge($config, $config[$type]);
        }

        // if there is any particular configurator passed, run it first
        if (is_callable($configMutator)) {
            $config = $configMutator($config);
        }

        $con = $this->createFileMakerConnection($config);

        if (!array_key_exists('cache', $config) || $config['cache']) {
            $this->connections[$type] = $con;
        }
        return $con;
    }

    protected function createFileMakerConnection($config)
    {
        $fm = new \fmPDA(
            $config['database'],
            $config['host'],
            $config['username'],
            $config['password']
        );
        $fm->setProperty('recordClass', \FMLaravel\Database\FileMaker\Record::class);

        if (array_key_exists('properties', $config) && is_array($config['properties'])) {
            foreach ($config['properties'] as $key => $value) {
                $fm->setProperty($key, $value);
            }
        }

        if (array_key_exists('logger', $config) && $config['logger'] instanceof LogFacade) {
            $config['logger']->attachTo($fm);
        } elseif (array_key_exists('logLevel', $config)) {
            switch ($config['logLevel']) {
                case 'error':
                    LogFacade::with(FILEMAKER_LOG_ERR)->attachTo($fm);
                    break;
                case 'info':
                    LogFacade::with(FILEMAKER_LOG_INFO)->attachTo($fm);
                    break;
                case 'debug':
                    LogFacade::with(FILEMAKER_LOG_DEBUG)->attachTo($fm);
                    break;
            }
        }

        return $fm;
    }


    /**
     * Returns an array of databases that are available with the current
     * server settings and the current user name and password
     * credentials.
     *
     * @return array|FileMaker_Error List of database names or an Error object.
     * @see FileMaker
     */
    public function listDatabases()
    {
        return $this->filemaker('read')->listDatabases();
    }

    /**
     * Returns an array of ScriptMaker scripts from the current database that
     * are available with the current server settings and the current user
     * name and password credentials.
     *
     * @return array|FileMaker_Error List of script names or an Error object.
     * @see FileMaker
     */
    public function listScripts()
    {
        return $this->filemaker('read')->listScripts();
    }

    /**
     * Returns an array of layouts from the current database that are
     * available with the current server settings and the current
     * user name and password credentials.
     *
     * @return array|FileMaker_Error List of layout names or an Error object.
     * @see FileMaker
     */
    public function listLayouts()
    {
        return $this->filemaker('read')->listLayouts();
    }

    /**
     * Returns a Layout object that describes the specified layout.
     *
     * @param string $layout Name of the layout to describe.
     *
     * @return FileMaker_Layout|FileMaker_Error Layout or Error object.
     */
    public function getLayout($layoutName)
    {
        return $this->filemaker('read')->getLayout($layoutName);
    }


    /**
     * Returns the list of defined values in the specified value list.
     *
     * @param string $valueList Name of value list.
     * @param string  $recid Record from which the value list should be
     *        displayed.
     *
     * @return array List of defined values.

     * @deprecated Use getValueListTwoFields instead.

     * @see getValueListTwoFields
     */
    public function getValueList($layoutName, $valueListName, $recId = null)
    {
        return $this->filemaker('read')->getLayout($layoutName)->getValueList($valueListName, $recId);
    }
}
