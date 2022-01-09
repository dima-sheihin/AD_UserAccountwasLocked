try {

  $DcName = (Get-ADDomain).pdcemulator
  $Pdce = (Get-AdDomain).PDCEmulator

  $events = Get-WinEvent -ComputerName $Pdce -FilterHashtable @{logname='Security'; StartTime=(Get-Date).AddSeconds(-60); EndTime=(Get-Date); ID=4740; } -ErrorAction SilentlyContinue
  # Для отладки:
  # $Events | get-member -membertype *property # | format-list  -172800
  # $Events | Format-List
  $count=0;
  $arr=@{}
  $Events | foreach {
    $num = "str-$count"
    $arr[$num] = @{}
    $arr[$num]["TimeCreated"] = $_.TimeCreated | Get-Date -format "yyyy-MM-dd HH:mm:ss"
    $arr[$num]["TaskDisplayName"] = $_.TaskDisplayName
    $arr[$num]["Task"] = $_.Task
    $arr[$num]["Id"] = $_.Id
    $arr[$num]["LogName"] = $_.LogName
    $arr[$num]["ProviderName"] = $_.ProviderName
    $arr[$num]["LevelDisplayName"] = $_.LevelDisplayName
    $arr[$num]["MachineName"] = $_.MachineName
    $arr[$num]["UserName"] = $_.Properties[0].Value
    $arr[$num]["ComputerName"] = $_.Properties[1].Value
    $arr[$num]["DomainDC"] = $_.Properties[4].Value
    $arr[$num]["DomainName"] = $_.Properties[5].Value
    $arr[$num]["Message"] = $_.Message
    # $arr[$num]["UserID"] = $_.Properties[2].Value
    # $arr[$num]["ComputerID"] = $_.Properties[3].Value
    $count++;
    }

  if ( $count -eq 0 ) {
    Write-Host "not found locked account";
    Invoke-Expression "& `"C:\Zabbix\zabbix_sender-i386.exe`" -c C:\Zabbix\zabbix_agentd.conf -k UserAccountwasLocked -o 0"
    exit
    }
  $json = $(ConvertTo-Json -InputObject $arr ) # -Compress )

  [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
  $credential = New-Object System.Management.Automation.PSCredential( $addition_data_log_user , $(ConvertTo-SecureString $addition_data_log_pass -AsPlainText -Force) )
  $boundary = [System.Guid]::NewGuid().ToString()
  $LF = "`r`n"
  $bodyLines = ( "--$boundary", "Content-Disposition: form-data; name=`"file`"; filename=`"addition-data-log`"", "Content-Type: application/octet-stream$LF", $json, "--$boundary--$LF" ) -join $LF

  $result = Invoke-RestMethod $addition_data_log_url -Credential $credential -Method Post -ContentType "multipart/form-data; boundary=`"$boundary`"" -Body $bodyLines

  if (  ($result.IndexOf("filename-log-complete")) -ge 10 ) {
    # -1 - не найдена
    # 50 - найдена
    Write-Host "found locked account [$count], and file load to web server - complete";
    Invoke-Expression "& `"C:\Zabbix\zabbix_sender-i386.exe`" -c C:\Zabbix\zabbix_agentd.conf -k UserAccountwasLocked -o $count"
    }  
  }
catch {
  $Failure = $_.Exception.Response
  }
  
