<?php

require_once ("softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

/**
 * Description of OmicromSession
 *
 * @author rolando
 */
class OmicromSession {

    private $request;
    private $nameSession;
    private $defaultCriteriaField;
    private $defaultOrderField;
    private $arrayFilter;
    private $buttonAction;

    /**
     * 
     * @param string $defaultCriteriaField Campo por defecto para el filtrado de la información
     * @param string $defaultOrderField Campo por defecto para el ordenamiento
     * @param string $nameSession Nombre de la sesion para array bidimensional
     * @param array $arrayFilter Arreglo con parametros extras
     * @param string $buttonAction Nombre del objeto que acciona el llenado de los parametros extras
     */
    function __construct($defaultCriteriaField = "", $defaultOrderField = "", $nameSession = null, $arrayFilter = null, $buttonAction = null) {

        session_start();

        $this->request = utils\Request::instance();
        $this->defaultCriteriaField = $defaultCriteriaField;
        $this->defaultOrderField = $defaultOrderField;
        $this->nameSession = $nameSession;
        $this->arrayFilter = $arrayFilter;
        $this->buttonAction = $buttonAction;

        $criteria = $this->request->get("criteria");
        if ($criteria === "ini") {

            if ($this->nameSession != null) {
                utils\HTTPUtils::setSessionBiValue($this->nameSession, "page", "0");
                utils\HTTPUtils::setSessionBiValue($this->nameSession, "criteria", "");
                utils\HTTPUtils::setSessionBiValue($this->nameSession, "criteriaField", $this->defaultCriteriaField);
                utils\HTTPUtils::setSessionBiValue($this->nameSession, "sortField", $this->defaultOrderField);
                utils\HTTPUtils::setSessionBiValue($this->nameSession, "sortType", "ASC");
                utils\HTTPUtils::setSessionBiValue($this->nameSession, "returnLink", $this->request->get("returnLink"));
                utils\HTTPUtils::setSessionBiValue($this->nameSession, "backLink", $this->request->get("backLink"));
            } else {
                utils\HTTPUtils::setSessionValue("page", "0");
                utils\HTTPUtils::setSessionValue("criteria", "");
                utils\HTTPUtils::setSessionValue("criteriaField", $this->defaultCriteriaField);
                utils\HTTPUtils::setSessionValue("sortField", $this->defaultOrderField);
                utils\HTTPUtils::setSessionValue("sortType", "ASC");
                utils\HTTPUtils::setSessionValue("returnLink", $this->request->get("returnLink"));
                utils\HTTPUtils::setSessionValue("backLink", $this->request->get("backLink"));
            }
            if (is_array($this->arrayFilter) && count($this->arrayFilter) > 0) {
                foreach ($this->arrayFilter as $key => $defautl_value) {
                    if ($this->nameSession != null) {
                        utils\HTTPUtils::setSessionBiValue($this->nameSession, $key, $defautl_value);
                    } else {
                        utils\HTTPUtils::setSessionValue($key, $defautl_value);
                    }
                }
            }
        } else {

            if ($this->nameSession != null) {
                if ($this->request->has("page")) {
                    utils\HTTPUtils::setSessionBiValue($this->nameSession, "page", $this->request->get("page"));
                }
                if ($this->request->has("criteria")) {
                    utils\HTTPUtils::setSessionBiValue($this->nameSession, "criteria", $this->request->get("criteria"));
                }
                if ($this->request->has("sortField")) {
                    utils\HTTPUtils::setSessionBiValue($this->nameSession, "sortField", $this->request->get("sortField"));
                }
                if ($this->request->has("criteriaField")) {
                    utils\HTTPUtils::setSessionBiValue($this->nameSession, "criteriaField", $this->request->get("criteriaField"));
                }
                if ($this->request->has("sortType")) {
                    utils\HTTPUtils::setSessionBiValue($this->nameSession, "sortType", $this->request->get("sortType"));
                }
                if ($this->request->has("returnLink")) {
                    utils\HTTPUtils::setSessionBiValue($this->nameSession, "returnLink", $this->request->get("returnLink"));
                }
                if ($this->request->has("backLink")) {
                    utils\HTTPUtils::setSessionBiValue($this->nameSession, "backLink", $this->request->get("backLink"));
                }
            } else {
                if ($this->request->has("page")) {
                    utils\HTTPUtils::setSessionValue("page", $this->request->get("page"));
                }
                if ($this->request->has("criteria")) {
                    utils\HTTPUtils::setSessionValue("criteria", $this->request->get("criteria"));
                }
                if ($this->request->has("sortField")) {
                    utils\HTTPUtils::setSessionValue("sortField", $this->request->get("sortField"));
                }
                if ($this->request->has("criteriaField")) {
                    utils\HTTPUtils::setSessionValue("criteriaField", $this->request->get("criteriaField"));
                }
                if ($this->request->has("sortType")) {
                    utils\HTTPUtils::setSessionValue("sortType", $this->request->get("sortType"));
                }
                if ($this->request->has("returnLink")) {
                    utils\HTTPUtils::setSessionValue("returnLink", $this->request->get("returnLink"));
                }
                if ($this->request->has("backLink")) {
                    utils\HTTPUtils::setSessionValue("backLink", $this->request->get("backLink"));
                }
            }

            if ($this->request->has($this->buttonAction)) {
                if (is_array($arrayFilter) && count($arrayFilter) > 0) {
                    //error_log("send parameters...");
                    foreach ($this->arrayFilter as $key => $defautl_value) {
                        if ($this->nameSession != null) {
                            utils\HTTPUtils::setSessionBiValue($this->nameSession, $key, $this->request->get($key));
                        } else {
                            utils\HTTPUtils::setSessionValue($key, $this->request->get($key));
                        }
                    }
                }
            }
        }
    }

    public function getSessionAttribute($key) {
        if ($this->nameSession != null) {
            return utils\HTTPUtils::getSessionBiValue($this->nameSession, $key);
        } else {
            return utils\HTTPUtils::getSessionValue($key);
        }
    }

}
