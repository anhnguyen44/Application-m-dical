

Param(
    [string]$cryptkey,
    [string]$dbname,
    [switch]$check_integrity,
    [int]$rotation, # 0 pour désactiver, un entier naturel positif sui dit combien de sauvegardes on garde
    [string]$frequency, # 0 pour désactiver
    [string]$hour,
    [string]$name,
    [string]$password
);

ipmo ScheduledTasks 

$CREATE_BACKUP_SCRIPT = "../scripts/create_backup.ps1"
$WORKING_PATH = (Get-Location).ToString()

$user = (New-Object System.Security.Principal.NTAccount('Administrateur')).Translate([System.Security.Principal.SecurityIdentifier]).value

$schedule = new-object -com Schedule.Service
$schedule.connect()
$tasks = $schedule.getfolder("").gettasks(0)

foreach($task in $tasks) {

    if($task.name -eq "PlanSave") {

        if( $frequency -eq 0) {
            Disable-ScheduledTask -TaskName "PlanSave"
        }
        else {
            if($task.State -eq 1) {
                Enable-ScheduledTask -TaskName "PlanSave"
            }
            $action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument " -file $CREATE_BACKUP_SCRIPT -ExecutionPolicy Bypass -dbname $dbname -cryptkey $cryptkey -rotation $rotation" -WorkingDirectory $WORKING_PATH
            $trigger = New-ScheduledTaskTrigger -Daily -DaysInterval $frequency -At $hour
            Set-ScheduledTask "PlanSave" -Action $action -Trigger $trigger -User $name -Password $password
        }

        exit
    
    }

}


if( $frequency -ne 0) {
    $action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument " -file $CREATE_BACKUP_SCRIPT -ExecutionPolicy Bypass -dbname $dbname -cryptkey $cryptkey -rotation $rotation" -WorkingDirectory $WORKING_PATH
    $trigger = New-ScheduledTaskTrigger -Daily -DaysInterval $frequency -At $hour
    Register-ScheduledTask -TaskName "PlanSave"  -Trigger $trigger -Action $action -Description "Planified Save Task" -User $name -Password $password
}