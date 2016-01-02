<?php
/**
 * /classes/DomainMOD/System.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2016 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
?>
<?php
namespace DomainMOD;

class System
{

    public function installCheck($connection, $web_root)
    { //@formatter:off
        $full_install_path = DIR_ROOT . "install/";
        if (is_dir($full_install_path) &&
            !mysqli_num_rows(mysqli_query($connection, "SHOW TABLES LIKE '" . `dw_servers` . "'"))) {
            $installation_mode = 1;
            $result_message = "<a href=\"" . $web_root . "/install/\">Click here to install</a><BR>";
        } else {
            $installation_mode = 0;
            $result_message = '';
        }
        return array($installation_mode, $result_message);
    } //@formatter:on

    public function checkVersion($connection, $current_version)
    {
        $live_version = $this->getLiveVersion();
        if ($current_version < $live_version && $live_version != '') {
            $sql = "UPDATE settings SET upgrade_available = '1'";
            mysqli_query($connection, $sql);
            $_SESSION['s_system_upgrade_available'] = '1';
            $message = $this->getUpgradeMessage();
        } else {
            $_SESSION['s_system_upgrade_available'] = '0';
            $message = "No Upgrade Available";
        }
        return $message;
    }

    public function getLiveVersion()
    {
        return file_get_contents('https://raw.githubusercontent.com/domainmod/domainmod/master/version.txt');
    }

    public function getUpgradeMessage()
    {
        return "A new version of DomainMOD is available for download. <a target=\"_blank\"
                href=\"http://domainmod.org/upgrade/\">Click here for upgrade instructions</a>.<BR>";
    }

    public function pageTitle($software_title, $page_title)
    {
        return $software_title . " :: " . $page_title;
    }

    public function pageTitleSub($software_title, $page_title, $page_subtitle)
    {
        return $software_title . " :: " . $page_title . " :: " . $page_subtitle;
    }

    public function checkExistingAssets($connection)
    {
        $queryB = new QueryBuild();

        $sql = $queryB->singleAsset('registrars');
        $_SESSION['s_has_registrar'] = $this->checkForRows($connection, $sql);
        $sql = $queryB->singleAsset('registrar_accounts');
        $_SESSION['s_has_registrar_account'] = $this->checkForRows($connection, $sql);
        $sql = $queryB->singleAsset('domains');
        $_SESSION['s_has_domain'] = $this->checkForRows($connection, $sql);
        $sql = $queryB->singleAsset('ssl_providers');
        $_SESSION['s_has_ssl_provider'] = $this->checkForRows($connection, $sql);
        $sql = $queryB->singleAsset('ssl_accounts');
        $_SESSION['s_has_ssl_account'] = $this->checkForRows($connection, $sql);
        $sql = $queryB->singleAsset('ssl_certs');
        $_SESSION['s_has_ssl_cert'] = $this->checkForRows($connection, $sql);
        return true;
    }

    public function checkForRows($connection, $sql)
    { //@formatter:off
        $result = mysqli_query($connection, $sql);
        if (mysqli_num_rows($result) >= 1) { return '1'; } else { return '0'; }
    } //@formatter:on

    public function checkForRowsResult($connection, $sql)
    { //@formatter:off
        $result = mysqli_query($connection, $sql);
        if (mysqli_num_rows($result) >= 1) { return $result; } else { return '0'; }
    } //@formatter:on

    public function authCheck()
    {
        if ($_SESSION['s_is_logged_in'] != 1) {
            $_SESSION['s_user_redirect'] = $_SERVER["REQUEST_URI"];
            $_SESSION['s_result_message'] = "You must be logged in to access this area<BR>";
            header("Location: " . $_SESSION['s_web_root'] . "/");
            exit;
        }
    }

    public function loginCheck()
    {
        if ($_SESSION['s_is_logged_in'] == 1) {
            header("Location: " . $_SESSION['s_web_root'] . "/domains.php");
            exit;
        }
    }

    public function checkAdminUser($is_admin, $web_root)
    {
        if ($is_admin !== 1) {
            header("Location: " . $web_root . "/invalid.php");
            exit;
        }
    }

    public function showResultMessage($result_message)
    { //@formatter:off
        ob_start(); ?>
        <div class="result_message_outer">
            <div class="result_message_inner">
                <?php echo $result_message; ?>
            </div>
        </div><?php
        return ob_get_clean();
    } //@formatter:on

}
