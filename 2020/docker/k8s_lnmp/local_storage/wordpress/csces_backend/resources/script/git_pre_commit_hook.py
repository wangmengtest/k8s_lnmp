#!/usr/bin/env python
# coding=utf8

import re
import os
import sys
import commands


class IgnoreCodeAnchor:
    """忽略组件内嵌入代码的钩子"""

    reg = r"#\s{0,}vhallEOF-.*-start\s*?\n([\s\S]*?)\s*?#\s{0,}vhallEOF-.*-end"
    suffix = '.pre-commit'

    def filterBlankLine(self, content):
        return "\n".join(
            filter(lambda c: c.strip() != "", content.split('\n')))

    def gitIgnoreAdd(self):
        gitIgnorePath = './.gitignore'
        if not os.path.isfile(gitIgnorePath):
            self.writeFile(gitIgnorePath, "*.pre-commit")
            return

        content = self.readFile(gitIgnorePath)
        mathchRes = re.search('\*' + self.suffix, content, re.M | re.I)
        if not mathchRes:
            content = self.filterBlankLine(
                content) + "\n*" + self.suffix + "\n"
            self.writeFile(gitIgnorePath, content)

    def removePlaceholderCode(self, file):
        """
        删除文件中占位符之间的代码
        """
        content = self.readFile(file)
        if not content:
            return False

        mathchList = re.findall(self.reg, content, re.M | re.I)

        newContent = content
        for item in mathchList:
            if item.strip():
                newContent = newContent.replace(item, "")

        if content != newContent:
            # 文件备份
            self.writeFile(file + self.suffix, content)

            self.writeFile(file, newContent)
            return True

        return False

    def readFile(self, file):
        with open(file, mode='r') as fp:
            return fp.read()
        return ""

    def writeFile(self, file, content):
        with open(file, mode='w') as fp:
            fp.write(content)

    def gitAdd(self, changeFiles):
        for file in changeFiles:
            if self.suffix in file:
                continue
            cmd = 'git add %s' % file
            os.system(cmd)

    def getCommitFileCount(self):
        cmd = 'git diff --cached --name-status | wc -l'
        count = commands.getoutput(cmd)
        return int(count)

    def getChangeFiles(self):
        changeFiles = list()
        cmd = '''git diff --cached --name-status | grep '.php$' | awk -F' ' '$1 !="D" && $1 !~/^R\d*/ {print $2}' | uniq'''

        files = commands.getoutput(cmd)
        if not files:
            return changeFiles

        for file in files.split('\n'):
            isMatch = self.removePlaceholderCode(file)
            if isMatch:
                changeFiles.append(file)

        return changeFiles

    def printChangedFiles(self, changeFiles):
        if changeFiles:
            print("----------- change file list -----------")
            for file in changeFiles:
                print("\033[32m" + file + "\033[0m")

            print("")

    def recoveryFiles(self, changeFiles):
        """
        将备份的文件恢复
        """
        for file in changeFiles:
            os.system("rm -f %s && mv %s%s %s" %
                      (file, file, self.suffix, file))

    def run(self):
        changeFiles = self.getChangeFiles()
        self.printChangedFiles(changeFiles)

        # gitIgnoreAdd()
        self.gitAdd(changeFiles)

        count = self.getCommitFileCount()
        self.recoveryFiles(changeFiles)
        if count == 0:
            print(
                "\033[31mcommit stop, nothing to commit, working tree clean\033[0m")
            sys.exit(1)
        sys.exit(0)


class Phpcs:
    """使用 phpcs 检查要提交的代码"""
    PHPCS_BIN = "php artisan qa:phpcs"

    def getChangeFiles(self):
        cmd = '''git diff --cached --name-status | grep '.php$' | awk -F' ' '$1 !="D" && $1 !~/^R\d*/ {print $2}' | uniq'''
        return commands.getoutput(cmd).split('\n')

    def run(self):
        changeFiles = self.getChangeFiles()
        if not changeFiles:
            return True

        result = 0  # 正常状态为 0，警告为 1，错误为 2
        for file in changeFiles:
            if not file.strip():
                continue

            cmd = "{} {}".format(self.PHPCS_BIN, file)
            output = commands.getoutput(cmd)
            if output.find('good') != -1: # 代码符合规范
                continue

            # phpcs 有报错误
            print(output)
            if output.find('ERROR AFFECTING') != -1:
                result = 2

            if output.find('WARNING') != -1:
                flag = input(
                    "\033[31mphpcs : code warning, continue commit ? \033[0m (\033[31my\033[0m/n)")
                if flag.strip() == 'n' and result < 1:
                    result = 1

        return result == 0

    def main(self):
        path = os.path.abspath('.')
        while True:
            if os.path.isfile(path + "/artisan"):
                break
            path = os.path.dirname(path)

        # 切换到项目根目录
        os.chdir(path)
        if not self.run():
            sys.exit(1)


if __name__ == "__main__":

    # 检查提交代码的编码规范
    Phpcs().main()

    # 检查是否是组件目录
    if os.path.abspath('.').rfind('vhall-component') != -1:
        # 忽略嵌入代码的锚点
        IgnoreCodeAnchor().run()
