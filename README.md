# ddev-codebase

This is a DDEV add-on for [Codebase](https://www.codebasehq.com), a project managment
software.

## Add-on installation

```
ddev add-on get happiness/ddev-codebase
```

## PhpStorm Integration

```xml
<component name="TaskManager">
  <servers>
    <Generic url="https://ki.ddev.site:8081">
      <option name="loginAnonymously" value="true" />
      <option name="responseHandlers">
        <XPathResponseHandler />
        <JsonResponseHandler>
          <selectors>
            <selector name="tasks" path="$.tickets" />
            <selector name="id" path="id" />
            <selector name="summary" path="summary" />
            <selector name="description" path="" />
            <selector name="updated" path="updated" />
            <selector name="created" path="created" />
            <selector name="closed" />
            <selector name="issueUrl" path="issue_url" />
            <selector name="singleTask-id" path="$.ticket.id" />
            <selector name="singleTask-summary" path="$.ticket.summary" />
            <selector name="singleTask-description" path="" />
            <selector name="singleTask-updated" path="$.ticket.updated" />
            <selector name="singleTask-created" path="$.ticket.created" />
            <selector name="singleTask-closed" />
            <selector name="singleTask-issueUrl" path="$.ticket.issue_url" />
          </selectors>
        </JsonResponseHandler>
        <RegExResponseHandler />
      </option>
      <option name="singleTaskUrl" value="{serverUrl}/codebase.php?op=ticket&amp;id={id}" />
      <option name="tasksListUrl" value="{serverUrl}/codebase.php?op=tickets" />
    </Generic>
  </servers>
</component>
```
