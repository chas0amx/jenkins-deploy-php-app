pipeline {
  agent {
    kubernetes {
      defaultContainer 'jnlp'
      yaml """
apiVersion: v1
kind: Pod
metadata:
labels:
  component: ci
spec:
  # Use service account that can deploy to all namespaces
  serviceAccountName: jenkins
  containers:
  - name: kubectl
    image: gcr.io/cloud-builders/kubectl
    command:
    - cat
    tty: true
"""
}
  }
  stages {
    stage('Deploy Production') {
      // Production branch
     // when { branch 'master' }
      steps{
        container('kubectl') {
          sh "kubectl delete pod -l app=web -n default"
        }
      }
    }
     
    }
  }
